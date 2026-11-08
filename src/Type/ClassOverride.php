<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\Concrete;

/**
 * Resolves generated factory methods, preserving the no-XML registration assumption.
 */
final class ClassOverride
{
    private function __construct()
    {
    }

    public static function calledType(StaticCall $call, Scope $scope, string $method, ?RegisteredClassOverrides $overrides = null): ?Type
    {
        $type = $call->class instanceof Name
            ? $scope->resolveTypeByName($call->class)
            : $scope->getType($call->class)->getClassStringObjectType();
        // Explicit names must not inherit the scope's $this type; preserve static:: for late static binding.
        if ($call->class instanceof Name && strtolower($call->class->toString()) !== 'static') {
            $type = new ObjectType($scope->resolveName($call->class));
        }

        $classes = $type->getObjectClassReflections();
        if ($classes === []) {
            return null;
        }

        $hasGeneratedClass = false;
        $hasOverride = false;
        foreach ($classes as $class) {
            $isIntermediate = $overrides?->configured($scope) && $overrides->loadedClass($class, $scope)?->isObject()->yes();
            if (($class->isAbstract() && !$isIntermediate) || !self::inheritsGeneratedMethod($class, $method)) {
                return null;
            }

            if (self::isGenerated($class)) {
                $hasGeneratedClass = true;
            } else {
                $hasOverride = true;
            }
        }

        if ($overrides?->configured($scope)) {
            $types = [];
            foreach ($classes as $class) {
                $loaded = $overrides->loadedClass($class, $scope);
                if ($loaded === null) {
                    return null;
                }
                $types[] = $loaded;
            }

            return TypeCombinator::union(...$types);
        }

        return $hasGeneratedClass && $hasOverride ? null : $type;
    }

    /**
     * An iterable intersection carries the override through PHPStan's ordinary static/$this return handling,
     * while preserving the generated Listing class and all of its methods.
     */
    public static function listingType(Type $listing, Type $calledType, bool $force = false): Type
    {
        foreach ($calledType->getObjectClassReflections() as $class) {
            if ($force) {
                return new ListingIterableType($listing, $calledType);
            }
            if (!self::isGenerated($class)) {
                return TypeCombinator::intersect($listing, new IterableType(new MixedType(), $calledType));
            }
        }

        return $listing;
    }

    public static function listingElementType(Type $type, bool $allowGenerated = false): ?Type
    {
        $element = $type->getIterableValueType();
        if (!$element->isObject()->yes()) {
            return null;
        }

        $classes = $element->getObjectClassReflections();
        if ($classes === []) {
            return null;
        }

        foreach ($classes as $class) {
            if (!$class->isSubclassOf(Concrete::class) || (!$allowGenerated && self::isGenerated($class))) {
                return null;
            }
        }

        return $element;
    }

    private static function isGenerated(ClassReflection $class): bool
    {
        return str_starts_with($class->getName(), 'Pimcore\\Model\\DataObject\\') && $class->isSubclassOf(Concrete::class);
    }

    private static function inheritsGeneratedMethod(ClassReflection $class, string $method): bool
    {
        for ($current = $class; $current !== null; $current = $current->getParentClass()) {
            if (self::isGenerated($current)) {
                return isset($current->getMethodTags()[$method]);
            }

            if (isset($current->getMethodTags()[$method])
                || ($current->hasNativeMethod($method)
                    && $current->getNativeMethod($method)->getDeclaringClass()->getName() === $current->getName())
            ) {
                return false;
            }
        }

        return false;
    }
}
