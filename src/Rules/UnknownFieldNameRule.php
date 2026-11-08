<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Type\GeneratedGetters;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\DataObject\Localizedfield;

/**
 * Constant field names passed to generic accessors and localized finders must name a field.
 * What counts as a field is decided by {@see GeneratedGetters}, the same lookups the generic getters are typed with.
 *
 * @implements Rule<Expr>
 */
final class UnknownFieldNameRule implements Rule
{
    private const array METHODS = ['get', 'getvalueforfieldname', 'set', 'getvaluefromparent', 'getlocalizedvalue', 'setlocalizedvalue'];

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ((!$node instanceof MethodCall && !$node instanceof StaticCall) || !$node->name instanceof Identifier) {
            return [];
        }

        $methodName = strtolower($node->name->toString());
        if ($node instanceof StaticCall ? $methodName !== 'getbylocalizedfields' : !in_array($methodName, self::METHODS, true)) {
            return [];
        }

        $receiverType = $node instanceof StaticCall
            ? ($node->class instanceof Name ? $scope->resolveTypeByName($node->class) : $scope->getType($node->class)->getClassStringObjectType())
            : $scope->getType($node->var);
        $localized = in_array($methodName, ['getlocalizedvalue', 'setlocalizedvalue'], true);
        $getters = GeneratedGetters::of($localized ? $receiverType->getTemplateType(Localizedfield::class, 'TOwner') : $receiverType);
        if ($getters === null) {
            return [];
        }

        $method = $scope->getMethodReflection($receiverType, $node->name->toString());
        if ($method === null) {
            return [];
        }

        // Named arguments are put in parameter order, so the field name is the first argument.
        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants());
        $call = $node instanceof StaticCall
            ? ArgumentsNormalizer::reorderStaticCallArguments($variant, $node)
            : ArgumentsNormalizer::reorderMethodArguments($variant, $node);
        $fieldNameArg = $call?->getArgs()[0] ?? null;
        if ($fieldNameArg === null) {
            return [];
        }

        $errors = [];
        foreach ($scope->getType($fieldNameArg->value)->getConstantStrings() as $fieldName) {
            $error = match ($methodName) {
                'get' => self::checkGet($getters, $fieldName->getValue()),
                'getvaluefromparent' => $getters->hasGetter($fieldName->getValue())
                    ? null
                    : self::unknown($getters, 'getValueFromParent', $fieldName->getValue()),
                'getlocalizedvalue', 'setlocalizedvalue', 'getbylocalizedfields' => $getters->isExactLocalizedField($fieldName->getValue(), $scope)
                    ? null
                    : RuleErrorBuilder::message(sprintf(
                        "Call to %s::%s() with unknown field name '%s'.",
                        $receiverType->describe(VerbosityLevel::typeOnly()),
                        $method->getName(),
                        $fieldName->getValue(),
                    )),
                'set' => $getters->type()->hasMethod('set' . ucfirst($fieldName->getValue()))->yes()
                    ? null
                    : self::unknown($getters, 'set', $fieldName->getValue()),
                default => self::checkGetValueForFieldName($getters, $fieldName->getValue(), $scope),
            };
            if ($error === null) {
                continue;
            }

            $errors[] = $error->identifier('pimcore.unknownFieldName')->build();
        }

        $fieldNameType = $scope->getType($fieldNameArg->value);
        $valueArg = $call->getArgs()[1] ?? null;
        $fieldNames = $fieldNameType->getConstantStrings();
        if ($methodName === 'set' && $errors === [] && count($fieldNames) === 1 && $fieldNameType->equals($fieldNames[0]) && $valueArg !== null && !$valueArg->unpack) {
            $setter = $scope->getMethodReflection($receiverType, 'set' . ucfirst($fieldNames[0]->getValue()));
            $parameter = $setter?->getOnlyVariant()->getParameters()[0] ?? null;
            $valueType = $scope->getType($valueArg->value);
            if ($parameter !== null && $parameter->getType()->accepts($valueType, $scope->isDeclareStrictTypes())->no()) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    "Parameter #2 \$value of %s::set('%s') expects %s, %s given.",
                    self::describe($getters),
                    $fieldNames[0]->getValue(),
                    $parameter->getType()->describe(VerbosityLevel::typeOnly()),
                    $valueType->describe(VerbosityLevel::typeOnly()),
                ))->identifier('pimcore.fieldValueType')->build();
            }
        }

        return $errors;
    }

    /**
     * `get('x')` calls `getX()`, whatever the case.
     *
     * @return RuleErrorBuilder<RuleError>|null
     */
    private static function checkGet(GeneratedGetters $getters, string $fieldName): ?RuleErrorBuilder
    {
        if ($getters->hasGetter($fieldName)) {
            return null;
        }

        return self::unknown($getters, 'get', $fieldName);
    }

    /**
     * `getValueForFieldName('x')` reads the property `$x`, so the case matters, and returns null if there is none (a
     * calculated value has none, but is computed).
     *
     * @return RuleErrorBuilder<RuleError>|null
     */
    private static function checkGetValueForFieldName(GeneratedGetters $getters, string $fieldName, Scope $scope): ?RuleErrorBuilder
    {
        if ($getters->isLocalizedField($fieldName, $scope)) {
            return RuleErrorBuilder::message(sprintf(
                "Call to %s::getValueForFieldName() with localized field '%s' always returns null.",
                self::describe($getters),
                $fieldName,
            ))->tip(sprintf(
                "Use get('%s', \$language) or %s(\$language) instead.",
                $fieldName,
                $getters->declaredGetterName($fieldName, $scope),
            ));
        }

        if ($getters->hasProperty($fieldName) || $getters->isCalculatedValue($fieldName, $scope)) {
            return null;
        }

        return self::unknown($getters, 'getValueForFieldName', $fieldName);
    }

    /**
     * @return RuleErrorBuilder<RuleError>
     */
    private static function unknown(GeneratedGetters $getters, string $method, string $fieldName): RuleErrorBuilder
    {
        return RuleErrorBuilder::message(sprintf(
            "Call to %s::%s() with unknown field name '%s'.",
            self::describe($getters),
            $method,
            $fieldName,
        ));
    }

    private static function describe(GeneratedGetters $getters): string
    {
        return $getters->type()->describe(VerbosityLevel::typeOnly());
    }
}
