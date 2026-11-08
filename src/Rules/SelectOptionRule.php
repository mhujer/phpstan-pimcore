<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\Type\GeneratedGetters;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Localizedfield;

/** @implements Rule<Expr> */
final class SelectOptionRule implements Rule
{
    public function __construct(private readonly ClassDefinitionReader $classDefinitionReader)
    {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $namedCall = NamedCall::of($node);
        if ($namedCall === null || $namedCall->node->isFirstClassCallable()) {
            return [];
        }

        $methodName = strtolower($namedCall->name);
        $values = in_array($methodName, ['create', 'setvalues', 'setvalue'], true);
        if ((!$values && !str_starts_with($methodName, 'set')) || ($methodName === 'create') !== ($namedCall->node instanceof StaticCall)) {
            return [];
        }

        $receiverType = $namedCall->receiverType($scope);
        $method = $scope->getMethodReflection($receiverType, $namedCall->name);
        if ($method === null) {
            return [];
        }

        if ($values) {
            foreach ($receiverType->getObjectClassReflections() as $class) {
                if (!ValuesArray::usesNativeSetters($class)) {
                    return [];
                }
            }

            // Generated create() delegates its values array to the native values methods.
            if ($methodName === 'create' && $method->getDeclaringClass()->getParentClass()?->getName() !== Concrete::class) {
                return [];
            }
        }

        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $namedCall->node->getArgs(), $method->getVariants());
        $args = ArgumentsNormalizer::reorderArgs($variant, $namedCall->node->getArgs());
        if ($args === null) {
            return [];
        }

        $entries = [];
        if ($values) {
            foreach (ValuesArray::read($methodName, $args, $scope) as [$fieldName, $value]) {
                if (!is_string($fieldName)) {
                    continue;
                }

                $entries[] = [$fieldName, $value];
            }
        } else {
            $generic = $methodName === 'set' || $methodName === 'setlocalizedvalue';
            $value = $args[$generic ? 1 : 0] ?? null;
            if ($value === null || $value->unpack) {
                return [];
            }

            if ($generic) {
                $fieldArg = $args[0] ?? null;
                if ($fieldArg === null || $fieldArg->unpack) {
                    return [];
                }

                foreach ($scope->getType($fieldArg->value)->getConstantStrings() as $string) {
                    $entries[] = [$string->getValue(), $value->value];
                }
            } else {
                $entries[] = [lcfirst(substr($method->getName(), 3)), $value->value];
            }
        }

        $ownerType = $methodName === 'setlocalizedvalue' ? TypeCombinator::removeNull($receiverType)->getTemplateType(Localizedfield::class, 'TOwner') : $receiverType;
        $errors = [];
        foreach ($ownerType->getObjectClassReflections() as $class) {
            $getters = GeneratedGetters::of(new ObjectType($class->getName()));
            if ($getters === null) {
                continue;
            }

            foreach ($entries as [$fieldName, $value]) {
                if (!$getters->hasGetter($fieldName) || ($methodName === 'setlocalizedvalue' && !$getters->isExactLocalizedField($fieldName, $scope))) {
                    continue;
                }

                foreach ($getters->definedFields($this->classDefinitionReader, $getters->declaredGetterName($fieldName, $scope), $scope) as $field) {
                    $options = $field?->staticOptionValues();
                    if ($options === null) {
                        continue;
                    }

                    $multiselect = $field->type === Multiselect::class;
                    foreach (self::strings($scope->getType($value), $multiselect) as $string) {
                        // Pimcore's validation uses loose comparison, and never validates empty values.
                        if ($string === '' || self::matchesOption($string, $options)) {
                            continue;
                        }

                        $message = sprintf(
                            "Value '%s' is not an option of %s field %s::$%s.",
                            $string,
                            $multiselect ? 'multiselect' : 'select',
                            substr($class->getName(), (int) strrpos($class->getName(), '\\') + 1),
                            $field->name,
                        );
                        $errors[$message] = RuleErrorBuilder::message($message)
                            ->identifier('pimcore.selectOption')
                            ->tip(self::tip($options, $string))
                            ->build();
                    }
                }
            }
        }

        return array_values($errors);
    }

    /** @return list<string> */
    private static function strings(Type $type, bool $multiselect): array
    {
        $types = [$type];
        if ($multiselect) {
            $types = [];
            foreach ($type->getConstantArrays() as $array) {
                array_push($types, ...$array->getValueTypes());
            }
        }

        $strings = [];
        foreach ($types as $valueType) {
            foreach ($valueType->getConstantStrings() as $string) {
                $strings[] = $string->getValue();
            }
        }

        return $strings;
    }

    /**
     * Pimcore compares numeric strings numerically, including differently padded values.
     *
     * @param non-empty-list<string> $options
     */
    private static function matchesOption(string $value, array $options): bool
    {
        foreach ($options as $option) {
            if (($value <=> $option) === 0) {
                return true;
            }
        }

        return false;
    }

    /** @param non-empty-list<string> $options */
    private static function tip(array $options, string $value): string
    {
        $tip = "Options: '" . implode("', '", array_slice($options, 0, 10)) . "'" . (count($options) > 10 ? ', …' : '') . '.';
        $closest = null;
        $distance = 3;
        foreach ($options as $option) {
            $candidateDistance = levenshtein($value, $option);
            if ($candidateDistance >= $distance) {
                continue;
            }

            $closest = $option;
            $distance = $candidateDistance;
        }

        return $closest === null ? $tip : $tip . " Did you mean '" . $closest . "'?";
    }
}
