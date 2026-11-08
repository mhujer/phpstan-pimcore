<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\Listing;

/**
 * A Listing builds its result arrays itself, so they are lists of the type the generated Listing declares.
 */
final class ListingReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const array LIST_METHODS = ['load', 'getData', 'getObjects', 'loadIdList'];

    public function __construct(private readonly RegisteredClassOverrides $overrides)
    {
    }

    public function getClass(): string
    {
        return Listing::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), [...self::LIST_METHODS, 'getItems', 'current'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $receiver = $scope->getType($methodCall->var);
        $element = ClassOverride::listingElementType($receiver, $this->overrides->configured($scope));
        if ($element === null && $this->overrides->configured($scope)) {
            $declared = self::declaredReturnType($methodReflection, $methodCall->getArgs(), $scope);
            $mapped = $this->overrides->replace($declared, $scope);
            if (!$mapped->equals($declared) && $methodReflection->getName() !== 'loadIdList') {
                return $methodReflection->getName() === 'current' ? $mapped : self::asList($mapped);
            }
        }
        if ($element !== null && $methodReflection->getName() !== 'loadIdList') {
            return $methodReflection->getName() === 'current'
                ? TypeCombinator::union($element, new ConstantBooleanType(false))
                : self::asList(new ArrayType(new IntegerType(), $element));
        }

        if ($methodReflection->getName() === 'getItems') {
            // getItems() pages through load(), which is where the generated Listing declares the element type.
            $loadMethod = $scope->getMethodReflection($scope->getType($methodCall->var), 'load');
            if ($loadMethod === null) {
                return null;
            }

            return self::asList($this->overrides->replace(self::declaredReturnType($loadMethod, [], $scope), $scope));
        }

        return self::asList(self::declaredReturnType($methodReflection, $methodCall->getArgs(), $scope));
    }

    /**
     * @param array<Arg> $args
     */
    private static function declaredReturnType(MethodReflection $methodReflection, array $args, Scope $scope): Type
    {
        return ParametersAcceptorSelector::selectFromArgs($scope, $args, $methodReflection->getVariants())
            ->getReturnType();
    }

    private static function asList(Type $type): ?Type
    {
        if (!$type->isArray()->yes()) {
            return null;
        }

        return TypeCombinator::intersect($type, new AccessoryArrayListType());
    }
}
