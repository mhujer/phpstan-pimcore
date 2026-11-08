<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * What a method declares it returns, and how a narrower type keeps the declared null.
 */
final class DeclaredReturnType
{
    private function __construct()
    {
    }

    /**
     * The return type of the method's first variant. Getters of generated classes have just one.
     */
    public static function of(MethodReflection $methodReflection): Type
    {
        return $methodReflection->getVariants()[0]->getReturnType();
    }

    /**
     * `$narrowedType`, plus null if the declared type may be null.
     */
    public static function withNullOf(Type $declaredType, Type $narrowedType): Type
    {
        return $declaredType->isNull()->no() ? $narrowedType : TypeCombinator::addNull($narrowedType);
    }
}
