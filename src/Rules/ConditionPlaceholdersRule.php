<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation;
use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation;
use Pimcore\Model\DataObject\Listing\Concrete as ConcreteListing;
use Pimcore\Model\Listing\AbstractListing;

/** @implements Rule<MethodCall> */
final class ConditionPlaceholdersRule implements Rule
{
    public function __construct(
        private readonly ClassDefinitionReader $classDefinitionReader,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier || $node->isFirstClassCallable()) {
            return [];
        }

        $name = $node->name->toLowerString();
        $filter = str_starts_with($name, 'filterby');
        if (!$filter && !in_array($name, ['setcondition', 'addconditionparam'], true)) {
            return [];
        }

        $receiver = $scope->getType($node->var);
        if (!(new ObjectType(AbstractListing::class))->isSuperTypeOf($receiver)->yes()) {
            return [];
        }

        $method = $scope->getMethodReflection($receiver, $name);
        if ($method === null) {
            return [];
        }

        if ($filter) {
            $declaringClass = $method->getDeclaringClass()->getName();
            if ($declaringClass !== ConcreteListing::class && preg_match('~^Pimcore\\\\Model\\\\DataObject\\\\[^\\\\]+\\\\Listing$~', $declaringClass) !== 1) {
                return [];
            }
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node->getArgs(),
        );
        if ($args === null || !isset($args[0]) || ($name === 'setcondition' && !isset($args[1]))) {
            return [];
        }

        if ($filter) {
            $operatorType = isset($args[1]) ? $scope->getType($args[1]->value) : null;
            $constants = $operatorType?->getConstantStrings() ?? [];
            if ($operatorType !== null && ($constants === [] || !TypeCombinator::union(...$constants)->isSuperTypeOf($operatorType)->yes())) {
                return [];
            }

            $strings = $operatorType === null ? ['='] : array_map(static fn ($constant): string => $constant->getValue(), $constants);
            $generatedClass = substr($declaringClass, 0, -strlen('\\Listing'));
            $getterName = 'get' . substr($method->getName(), strlen('filterBy'));
            $field = $this->classDefinitionReader->forGeneratedClass($generatedClass, $scope)?->fieldForGetter($getterName);
            if ($field !== null && $this->reflectionProvider->hasClass($field->type)) {
                $fieldClass = $this->reflectionProvider->getClass($field->type);
                if (!$fieldClass->hasNativeMethod('addListingFilter')) {
                    return [];
                }

                $filterMethod = $fieldClass->getNativeMethod('addListingFilter');
                if ($filterMethod->getDeclaringClass()->getName() === ManyToOneRelation::class) {
                    if (in_array('=', $strings, true)) {
                        return [];
                    }

                    return [RuleErrorBuilder::message(sprintf(
                        '%s::%s() only supports the "=" operator for a many-to-one relation.',
                        $receiver->describe(VerbosityLevel::typeOnly()),
                        $method->getName(),
                    ))->identifier('pimcore.relationFilterOperator')->build()];
                }

                // Relation filters can transform a list or an element into different SQL and bindings.
                if ($filterMethod->getDeclaringClass()->getName() !== Data::class
                    && ($filterMethod->getDeclaringClass()->getName() !== ManyToManyObjectRelation::class || in_array('=', $strings, true))
                ) {
                    return [];
                }
            }

            if ($field === null && $this->reflectionProvider->hasClass($generatedClass)) {
                $class = $this->reflectionProvider->getClass($generatedClass);
                if ($class->hasNativeMethod($getterName)) {
                    $returnType = TypeCombinator::removeNull($class->getNativeMethod($getterName)->getOnlyVariant()->getReturnType());
                    // Generated relation getters reveal element values even without a class definition.
                    if ($returnType->getObjectClassReflections() !== [] || $returnType->getIterableValueType()->getObjectClassReflections() !== []) {
                        return [];
                    }
                }
            }

            $strings = array_map(static fn (string $operator): string => str_contains($operator, '?') ? $operator : $operator . ' ?', $strings);
        } else {
            $strings = $this->sqlStrings($args[0]->value, $scope);
        }
        if ($strings === []) {
            return [];
        }

        $value = $filter ? $scope->getType($args[0]->value) : (isset($args[1]) ? $scope->getType($args[1]->value) : new NullType());
        $tokenizer = new ConditionPlaceholderTokenizer();
        foreach ($strings as $string) {
            $sql = $string;
            if ($name === 'addconditionparam' && $tokenizer->ignoresValue($sql)) {
                return [];
            }

            $error = $this->bindingError($tokenizer->tokenize($sql), $value, $name === 'setcondition');
            if ($error === null) {
                return [];
            }
        }

        [$message, $tip] = $error;
        $builder = RuleErrorBuilder::message(sprintf(
            'Argument #%d of %s::%s() %s',
            $filter ? 2 : 1,
            $receiver->describe(VerbosityLevel::typeOnly()),
            $method->getName(),
            $message,
        ))->identifier('pimcore.conditionPlaceholders');
        if ($tip !== null) {
            $builder->tip($tip);
        }

        return [$builder->build()];
    }

    /**
     * Numeric parts cannot introduce placeholders. Both signs cover a possible SQL comment after a literal '-'.
     * A dynamic number inside a named placeholder is skipped because its name cannot be established.
     * @return list<string>
     */
    private function sqlStrings(Expr $expr, Scope $scope): array
    {
        $type = $scope->getType($expr);
        $constants = $type->getConstantStrings();
        if ($constants !== [] && TypeCombinator::union(...$constants)->isSuperTypeOf($type)->yes()) {
            return array_map(static fn ($constant): string => $constant->getValue(), $constants);
        }

        $marker = '98765432109876543210';
        if (TypeCombinator::union(new IntegerType(), new FloatType())->isSuperTypeOf($type)->yes()) {
            return [$marker, '-' . $marker];
        }

        if ($expr instanceof Concat) {
            $parts = [$expr->left, $expr->right];
        } elseif ($expr instanceof InterpolatedString) {
            $parts = $expr->parts;
        } else {
            return [];
        }

        $strings = [''];
        foreach ($parts as $part) {
            $suffixes = $part instanceof InterpolatedStringPart ? [$part->value] : $this->sqlStrings($part, $scope);
            if ($suffixes === [] || count($strings) * count($suffixes) > 64) {
                return [];
            }

            $combined = [];
            foreach ($strings as $prefix) {
                foreach ($suffixes as $suffix) {
                    $sql = $prefix . $suffix;
                    if (preg_match('/:[a-zA-Z0-9_]*' . $marker . '/', $sql) === 1) {
                        return [];
                    }

                    $combined[] = $sql;
                }
            }

            $strings = array_values(array_unique($combined));
        }

        return $strings;
    }

    /**
     * @param list<string> $tokens
     * @return array{string, ?string}|null
     */
    private function bindingError(array $tokens, Type $value, bool $set): ?array
    {
        $count = count(array_filter($tokens, static fn (string $token): bool => $token === '?'));
        $names = array_values(array_unique(array_filter($tokens, static fn (string $token): bool => $token !== '?')));
        $arrays = $value->getConstantArrays();
        $keys = [];
        if ($value->isConstantArray()->yes() && count($arrays) === 1) {
            $array = $arrays[0];
            if ($array->getOptionalKeys() !== []) {
                return null;
            }

            $bound = 0;
            foreach ($array->getKeyTypes() as $key) {
                if ($key->isInteger()->yes()) {
                    $bound++;
                } else {
                    $keyValue = $key->getValue();
                    if (is_string($keyValue)) {
                        $keys[] = str_starts_with($keyValue, ':') ? substr($keyValue, 1) : $keyValue;
                    }
                }
            }

            $spread = count($array->getValueTypes()) > 1 && $array->getIterableValueType()->isArray()->no();
        } elseif ($value->isArray()->yes()) {
            if ($count === 1 && $names === [] && $value->getIterableValueType()->isArray()->no()) {
                return self::spreadError();
            }

            return null;
        } elseif ($value->isArray()->no() && ($value->isScalar()->yes() || $value->isNull()->yes())) {
            $bound = $set && $value->isNull()->yes() ? 0 : 1;
            $spread = false;
        } else {
            return null;
        }

        if ($count > 0 && $names !== []) {
            return ["mixes ? and :name placeholders, which PDO doesn't allow.", null];
        }

        if ($count === 1 && $spread) {
            return self::spreadError();
        }

        if (($count > 0 || $names === []) && $count !== $bound) {
            return [sprintf('has %d %s, but %d %s bound.', $count, $count === 1 ? 'placeholder' : 'placeholders', $bound, $bound === 1 ? 'parameter is' : 'parameters are'), null];
        }

        foreach ($names as $name) {
            if (!in_array(substr($name, 1), $keys, true)) {
                return [sprintf('uses %s, but no "%s" parameter is bound.', $name, substr($name, 1)), null];
            }
        }

        foreach ($keys as $key) {
            if (!in_array(':' . $key, $names, true)) {
                return [sprintf('binds "%s", which the condition doesn\'t use.', $key), null];
            }
        }

        if ($count !== $bound) {
            return [sprintf('has %d placeholders, but %d %s bound.', $count, $bound, $bound === 1 ? 'parameter is' : 'parameters are'), null];
        }

        return null;
    }

    /** @return array{string, string} */
    private static function spreadError(): array
    {
        return ['has 1 placeholder, but the array binds each element as its own parameter.', 'Wrap the array to bind it as one parameter: [$ids].'];
    }
}
