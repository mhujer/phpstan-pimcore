<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Doctrine\DBAL\Connection;
use Mhujer\PHPStan\Pimcore\Element\ElementType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\ExprPrinter;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\Listing\AbstractListing;

/**
 * The SQL string passed to a Listing must not be built from values that aren't known to be safe.
 *
 * @implements Rule<Expr>
 */
final class InterpolatedConditionRule implements Rule
{
    /**
     * The checked methods, lowercase, with the bound-parameter example for the tip. Null marks an identifier, which
     * Pimcore quotes unless `$quote` is false.
     */
    private const array METHODS = [
        'setcondition' => "setCondition('name = ?', [\$name])",
        'addconditionparam' => "addConditionParam('name = ?', \$name)",
        'setorderkey' => null,
        'setgroupby' => null,
    ];

    private const array QUOTING_METHODS = [
        AbstractListing::class => ['quote', 'quoteidentifier'],
        Connection::class => ['quote', 'quoteidentifier', 'quotesingleidentifier'],
    ];

    /**
     * A `sprintf()` conversion: optional argnum, flags, width, precision and the specifier.
     */
    private const string SPRINTF_CONVERSION = "/%(?:(\\d+)\\$)?(?:[-+ 0]|'.)*\\d*(?:\\.\\d+)?([bcdeEfFgGhHosuxX%])/";

    public function __construct(
        private readonly ExprPrinter $exprPrinter,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // PHPStan also visits a nullsafe call as a virtual MethodCall with its receiver narrowed.
        $namedCall = NamedCall::of($node);
        if ($namedCall === null || $node instanceof NullsafeMethodCall) {
            return [];
        }

        $methodName = strtolower($namedCall->name);
        if (in_array($methodName, ['getlist', 'setvalues', 'setvalue'], true)) {
            return $this->configErrors($namedCall, $scope);
        }

        if (!$node instanceof MethodCall) {
            return [];
        }

        if (!array_key_exists($methodName, self::METHODS)) {
            return [];
        }

        $receiverType = $scope->getType($node->var);
        if (!(new ObjectType(AbstractListing::class))->isSuperTypeOf($receiverType)->yes()) {
            return [];
        }

        $method = $scope->getMethodReflection($receiverType, $methodName);
        if ($method === null) {
            return [];
        }

        // Named arguments are put in parameter order: the SQL is the first argument, `$quote` the second.
        $call = ArgumentsNormalizer::reorderMethodArguments(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node,
        );
        $args = $call?->getArgs() ?? [];
        $sql = $args[0]->value ?? null;
        if ($sql === null || !$this->isBuiltString($sql, $scope)) {
            return [];
        }

        $isIdentifier = self::METHODS[$methodName] === null;
        if ($isIdentifier && (!isset($args[1]) || $scope->getType($args[1]->value)->isTrue()->yes())) {
            return [];
        }

        $unsafe = $this->firstUnsafePart($sql, $scope);
        if ($unsafe === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Argument #1 of %s::%s() embeds %s of type %s, which may allow SQL injection.',
                $receiverType->describe(VerbosityLevel::typeOnly()),
                $method->getName(),
                $this->exprPrinter->printExpr($unsafe),
                $scope->getType($unsafe)->describe(VerbosityLevel::precise()),
            ))
                ->tip($isIdentifier
                    ? 'Pass a constant identifier and leave $quote as true, so that Pimcore quotes it.'
                    : sprintf('Bind the value as a parameter instead, e.g. %s.', self::METHODS[$methodName]))
                ->identifier('pimcore.interpolatedCondition')
                ->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function configErrors(NamedCall $call, Scope $scope): array
    {
        $type = $call->receiverType($scope);
        $name = strtolower($call->name);
        $classes = $type->getObjectClassReflections();
        if (count($classes) !== 1 || !$classes[0]->hasNativeMethod($name)) {
            return [];
        }

        $method = $classes[0]->getNativeMethod($name);
        if (!$method->isPublic()) {
            return [];
        }

        $declaringClass = $method->getDeclaringClass()->getName();
        if ($name === 'getlist') {
            if (!$call->node instanceof StaticCall || ElementType::ofBaseClass($declaringClass) === null) {
                return [];
            }
        } elseif ($call->node instanceof StaticCall || !(new ObjectType(AbstractListing::class))->isSuperTypeOf($type)->yes() || !ValuesArray::usesNativeSetters($classes[0])) {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $call->node->getArgs(), $method->getVariants()),
            $call->node->getArgs(),
        );
        foreach (ValuesArray::read($name, $args ?? [], $scope) as [$key, $value]) {
            if (!is_string($key) || strtolower($key) !== 'condition' || !$this->isBuiltString($value, $scope)) {
                continue;
            }

            $unsafe = $this->firstUnsafePart($value, $scope);
            if ($unsafe === null) {
                continue;
            }

            return [RuleErrorBuilder::message(sprintf(
                "%s() option 'condition' embeds %s of type %s, which may allow SQL injection.",
                $method->getName(),
                $this->exprPrinter->printExpr($unsafe),
                $scope->getType($unsafe)->describe(VerbosityLevel::precise()),
            ))
                ->tip("A Listing config can't bind parameters: call setCondition('name = ?', [\$name]) on the listing instead, or quote the value with Db::get()->quote().")
                ->identifier('pimcore.interpolatedCondition')
                ->build()];
        }

        return [];
    }

    /**
     * Only a string built right in the argument is checked: a variable's origin can't be tracked.
     */
    private function isBuiltString(Expr $expr, Scope $scope): bool
    {
        return $expr instanceof Concat || $expr instanceof InterpolatedString || $this->builder($expr, $scope) !== null;
    }

    private function firstUnsafePart(Expr $expr, Scope $scope): ?Expr
    {
        if ($expr instanceof Concat) {
            return $this->firstUnsafePart($expr->left, $scope) ?? $this->firstUnsafePart($expr->right, $scope);
        }

        if ($expr instanceof InterpolatedString) {
            foreach ($expr->parts as $part) {
                if ($part instanceof InterpolatedStringPart) {
                    continue;
                }

                $unsafe = $this->firstUnsafePart($part, $scope);
                if ($unsafe !== null) {
                    return $unsafe;
                }
            }

            return null;
        }

        $builder = $this->builder($expr, $scope);
        if ($builder !== null) {
            [$function, $call] = $builder;

            return $function === 'sprintf' ? $this->firstUnsafeSprintfPart($call, $scope) : $this->firstUnsafeImplodePart($call, $scope);
        }

        if (self::isQuoteCall($expr, $scope) || self::isSafeType($scope->getType($expr))) {
            return null;
        }

        return $expr;
    }

    /**
     * A `sprintf()`, `implode()` or `join()` call, with its arguments in parameter order.
     *
     * @return array{'sprintf'|'implode', FuncCall}|null
     */
    private function builder(Expr $expr, Scope $scope): ?array
    {
        if (!$expr instanceof FuncCall || !$expr->name instanceof Name || !$this->reflectionProvider->hasFunction($expr->name, $scope)) {
            return null;
        }

        $function = $this->reflectionProvider->getFunction($expr->name, $scope);
        $name = match (strtolower($function->getName())) {
            'sprintf' => 'sprintf',
            'implode', 'join' => 'implode',
            default => null,
        };
        if ($name === null) {
            return null;
        }

        $call = ArgumentsNormalizer::reorderFuncArguments(
            ParametersAcceptorSelector::selectFromArgs($scope, $expr->getArgs(), $function->getVariants()),
            $expr,
        );

        return $call === null ? null : [$name, $call];
    }

    /**
     * Arguments that map to a numeric conversion are safe whatever their type. Those that map to `%s` are checked by
     * type, an unpacked argument by its value type.
     */
    private function firstUnsafeSprintfPart(FuncCall $call, Scope $scope): ?Expr
    {
        $args = $call->getArgs();
        if ($args === []) {
            return null;
        }

        $format = $args[0]->value;
        $formats = $scope->getType($format)->getConstantStrings();
        if ($formats === []) {
            return $this->firstUnsafePart($format, $scope) ?? $format;
        }

        $values = array_values(array_slice($args, 1));
        foreach ($formats as $constantFormat) {
            preg_match_all(self::SPRINTF_CONVERSION, $constantFormat->getValue(), $conversions, PREG_SET_ORDER);
            $next = 0;
            foreach ($conversions as [, $argnum, $specifier]) {
                if ($specifier === '%') {
                    continue;
                }

                if ($argnum === '') {
                    $index = $next;
                    $next++;
                } else {
                    $index = (int) $argnum - 1;
                }

                if ($specifier !== 's') {
                    continue;
                }

                $unsafe = $this->firstUnsafeValue($values, $index, $scope);
                if ($unsafe !== null) {
                    return $unsafe;
                }
            }
        }

        return null;
    }

    /**
     * @param list<Arg> $values
     */
    private function firstUnsafeValue(array $values, int $index, Scope $scope): ?Expr
    {
        foreach ($values as $position => $value) {
            if ($value->unpack) {
                // This and every later conversion take their value from the unpacked array.
                return self::isSafeType($scope->getType($value->value)->getIterableValueType()) ? null : $value->value;
            }

            if ($position === $index) {
                return $this->firstUnsafePart($value->value, $scope);
            }
        }

        return null;
    }

    /**
     * The separator is checked as a part, the array by its value type.
     */
    private function firstUnsafeImplodePart(FuncCall $call, Scope $scope): ?Expr
    {
        $args = $call->getArgs();
        if (count($args) === 1) {
            $array = $args[0];
        } elseif (count($args) === 2) {
            $unsafe = $this->firstUnsafePart($args[0]->value, $scope);
            if ($unsafe !== null) {
                return $unsafe;
            }

            $array = $args[1];
        } else {
            return null;
        }

        return self::isSafeType($scope->getType($array->value)->getIterableValueType()) ? null : $array->value;
    }

    /**
     * Constant scalars (a backed enum's `->value` included), and any `int`, `float`, `bool` or `null`.
     */
    private static function isSafeType(Type $type): bool
    {
        $safeScalars = new UnionType([new IntegerType(), new FloatType(), new BooleanType(), new NullType()]);
        foreach (TypeUtils::flattenTypes($type) as $member) {
            if (!$member->isConstantScalarValue()->yes() && !$safeScalars->isSuperTypeOf($member)->yes()) {
                return false;
            }
        }

        return true;
    }

    /**
     * `quote()` or `quoteIdentifier()` on a Listing, or one of the quoting methods of the Doctrine connection.
     */
    private static function isQuoteCall(Expr $expr, Scope $scope): bool
    {
        if (!$expr instanceof MethodCall && !$expr instanceof NullsafeMethodCall || !$expr->name instanceof Identifier) {
            return false;
        }

        $receiverType = $scope->getType($expr->var);
        foreach (self::QUOTING_METHODS as $class => $methods) {
            if (
                in_array($expr->name->toLowerString(), $methods, true)
                && (new ObjectType($class))->isSuperTypeOf($receiverType)->yes()
            ) {
                return true;
            }
        }

        return false;
    }
}
