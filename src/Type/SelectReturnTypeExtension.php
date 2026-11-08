<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;

/**
 * The getter of a select with static options returns an option value or an empty value, e.g. `''|'green'|'red'|null`.
 * A multiselect returns an array of option values (arbitrary keys, possibly empty), with its declared nullability.
 * Pimcore can persist an empty string even for mandatory selects, since drafts can skip mandatory validation.
 *
 * The options come from the class definition. It only narrows: the getter must be declared by the generated class,
 * and the literals must fit its declared type; otherwise the declared type stays. Selects with an options provider
 * keep the declared type, since the provider decides the options at runtime.
 *
 * Registered once per Pimcore class that generated classes extend.
 */
final class SelectReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param class-string $declaringClass
     */
    public function __construct(
        private readonly string $declaringClass,
        private readonly ClassDefinitionReader $classDefinitionReader,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strlen($methodReflection->getName()) > 3 && stripos($methodReflection->getName(), 'get') === 0;
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $getters = GeneratedGetters::of($scope->getType($methodCall->var));
        if ($getters === null) {
            return null;
        }

        $types = [];
        foreach ($getters->definedFields($this->classDefinitionReader, $methodReflection->getName(), $scope) as $field) {
            $options = $field?->staticOptionValues();
            if ($options === null) {
                return null;
            }

            $literals = [];
            foreach ($options as $option) {
                $literals[] = new ConstantStringType($option);
            }

            $types[] = $field->type === Multiselect::class
                ? new ArrayType(new MixedType(), TypeCombinator::union(...$literals))
                : TypeCombinator::union(new ConstantStringType(''), ...$literals);
        }

        $declaredType = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants())
            ->getReturnType();
        $narrowedType = DeclaredReturnType::withNullOf($declaredType, TypeCombinator::union(...$types));

        // A stale or odd definition that doesn't fit the generated getter is ignored.
        if (!$declaredType->isSuperTypeOf($narrowedType)->yes()) {
            return null;
        }

        return $narrowedType;
    }
}
