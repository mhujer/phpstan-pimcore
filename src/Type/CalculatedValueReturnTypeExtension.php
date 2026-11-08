<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\ClassDefinition\Data\CalculatedValue;

/** Class calculators return a string, or null when no calculator value is available. */
final readonly class CalculatedValueReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /** @param class-string $declaringClass */
    public function __construct(
        private string $declaringClass,
        private ClassDefinitionReader $classDefinitionReader,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return str_starts_with(strtolower($methodReflection->getName()), 'get');
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $getters = GeneratedGetters::of($scope->getType($methodCall->var));
        if ($getters === null) {
            return null;
        }

        foreach ($getters->definedFields($this->classDefinitionReader, $methodReflection->getName(), $scope) as $field) {
            $calculator = $field?->property('calculatorClass');
            if ($field?->type !== CalculatedValue::class || $field->property('calculatorType') !== 'class'
                || (is_string($calculator) && str_starts_with($calculator, '@'))
            ) {
                return null;
            }
        }

        $type = TypeCombinator::union(new StringType(), new NullType());
        $declared = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants())->getReturnType();

        return $declared->isSuperTypeOf($type)->yes() ? $type : null;
    }
}
