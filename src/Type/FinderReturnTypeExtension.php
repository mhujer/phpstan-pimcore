<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Listing\AbstractListing;

/**
 * Generated finders and getList() preserve the called class override in objects and Listing elements.
 * A finder returns the object or null when `limit == 1`, otherwise the Listing.
 */
final class FinderReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function __construct(
        private readonly PhpVersion $phpVersion,
        private readonly RegisteredClassOverrides $overrides,
    ) {
    }

    public function getClass(): string
    {
        return Concrete::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getList' || str_starts_with($methodReflection->getName(), 'getBy');
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): ?Type {
        $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $methodCall->getArgs(),
            $methodReflection->getVariants(),
        );
        $declaredType = $parametersAcceptor->getReturnType();

        $listingType = TypeCombinator::intersect($declaredType, new ObjectType(AbstractListing::class));
        if ($listingType->isObject()->no()) {
            return null;
        }

        $calledType = ClassOverride::calledType($methodCall, $scope, $methodReflection->getName(), $this->overrides);
        if ($calledType === null) {
            return null;
        }

        $listingElement = $calledType;
        if ($calledType->isNull()->yes()) {
            $receiver = $methodCall->class instanceof Name
                ? $scope->resolveTypeByName($methodCall->class)
                : $scope->getType($methodCall->class)->getClassStringObjectType();
            $elements = [];
            foreach ($receiver->getObjectClassReflections() as $class) {
                $generated = $this->overrides->generatedClass($class);
                if ($generated === null) {
                    continue;
                }

                $elements[] = new ObjectType($generated->getName());
            }
            if ($elements !== []) {
                $listingElement = TypeCombinator::union(...$elements);
            }
        }

        if ($methodReflection->getName() === 'getList') {
            $normalizedCall = ArgumentsNormalizer::reorderStaticCallArguments($parametersAcceptor, $methodCall);
            if ($normalizedCall === null) {
                return null;
            }

            foreach ($normalizedCall->getArgs() as $arg) {
                if ($arg->unpack || !$scope->getType($arg->value)->hasOffsetValueType(new ConstantStringType('class'))->no()) {
                    return null;
                }
            }

            return ClassOverride::listingType($this->overrides->replace($listingType, $scope), $listingElement, $calledType->isNull()->yes());
        }

        $listingType = ClassOverride::listingType($this->overrides->replace($listingType, $scope), $listingElement, $calledType->isNull()->yes());
        $finderType = TypeCombinator::union($calledType, $listingType, new NullType());

        $limitIndex = null;
        foreach ($parametersAcceptor->getParameters() as $index => $parameter) {
            if ($parameter->getName() !== 'limit') {
                continue;
            }

            $limitIndex = $index;
        }

        if ($limitIndex === null) {
            return null;
        }

        foreach ($methodCall->getArgs() as $arg) {
            if ($arg->unpack) {
                return $finderType;
            }
        }

        $normalizedCall = ArgumentsNormalizer::reorderStaticCallArguments($parametersAcceptor, $methodCall);
        if ($normalizedCall === null) {
            return $finderType;
        }

        $limitArg = $normalizedCall->getArgs()[$limitIndex] ?? null;
        if ($limitArg === null) {
            return $listingType;
        }

        $limitType = self::resolveLimitType($scope->getType($limitArg->value));
        if ($limitType === null) {
            return $finderType;
        }

        $isLimitOne = $limitType->looseCompare(new ConstantIntegerType(1), $this->phpVersion);
        if ($isLimitOne->isTrue()->yes()) {
            return TypeCombinator::addNull($calledType);
        }

        if ($isLimitOne->isFalse()->yes()) {
            return $listingType;
        }

        return $finderType;
    }

    /**
     * The array form (`['limit' => 1, 'offset' => 5]`) carries the limit in its `limit` key.
     * Returns null when it can't be told whether the array has that key.
     */
    private static function resolveLimitType(Type $argumentType): ?Type
    {
        $isArray = $argumentType->isArray();
        if ($isArray->no()) {
            return $argumentType;
        }

        if ($isArray->maybe()) {
            return null;
        }

        $limitKey = new ConstantStringType('limit');
        $hasLimitKey = $argumentType->hasOffsetValueType($limitKey);
        if ($hasLimitKey->no()) {
            return new NullType();
        }

        if ($hasLimitKey->maybe()) {
            return null;
        }

        return $argumentType->getOffsetValueType($limitKey);
    }
}
