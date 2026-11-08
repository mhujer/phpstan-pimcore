<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * The value accepted by a metadata column setter.
 */
final readonly class MetadataParameterReflection implements ParameterReflection
{
    public function getName(): string
    {
        return 'value';
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function getType(): Type
    {
        return new MixedType();
    }

    public function passedByReference(): PassedByReference
    {
        return PassedByReference::createNo();
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function getDefaultValue(): ?Type
    {
        return null;
    }
}
