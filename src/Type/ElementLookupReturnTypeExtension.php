<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\Element\ElementType;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\Element\Service;

final class ElementLookupReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Service::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getDeclaringClass()->getName() === Service::class
            && in_array(strtolower($methodReflection->getName()), ['getelementbyid', 'getelementbypath', 'getelementtype', 'getelementtypebyclassname'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if (in_array(strtolower($methodReflection->getName()), ['getelementtype', 'getelementtypebyclassname'], true)) {
            $declared = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants())->getReturnType();
            $type = TypeCombinator::union(...array_map(static fn (ElementType $elementType): Type => new ConstantStringType($elementType->value), ElementType::cases()));

            return DeclaredReturnType::withNullOf($declared, $type);
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants()),
            $methodCall->getArgs(),
        );
        if (!isset($args[0]) || $args[0]->unpack) {
            return null;
        }

        $type = $scope->getType($args[0]->value);
        $constants = $type->getConstantStrings();
        if ($constants === [] || !TypeCombinator::union(...$constants)->equals($type)) {
            return null;
        }

        $types = [new NullType()];
        foreach ($constants as $constant) {
            $elementType = ElementType::tryFrom($constant->getValue());
            if ($elementType === null) {
                continue;
            }

            $types[] = new ObjectType($elementType->elementClass());
        }

        return TypeCombinator::union(...$types);
    }
}
