<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\PhpDoc\Tag\MethodTagParameter;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;

/**
 * A finder parameter as its `@method` tag declares it, with the type {@see FinderMethodReflection} gives it.
 */
final readonly class FinderParameterReflection implements ParameterReflection
{
    public function __construct(
        private string $name,
        private Type $type,
        private MethodTagParameter $tagParameter,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isOptional(): bool
    {
        return $this->tagParameter->isOptional();
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function passedByReference(): PassedByReference
    {
        return $this->tagParameter->passedByReference();
    }

    public function isVariadic(): bool
    {
        return $this->tagParameter->isVariadic();
    }

    public function getDefaultValue(): ?Type
    {
        return $this->tagParameter->getDefaultValue();
    }
}
