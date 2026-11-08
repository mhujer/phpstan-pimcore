<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * A Listing whose iteration yields `$element`, keeping the Listing class and all of its methods.
 *
 * Used for an unregistered factory call, where the generated class must win over the override-aware Listing stub.
 * A plain `Listing&iterable<Generated>` intersection doesn't work there: PHPStan simplifies it to the stub's own
 * iterable type, so the generated class is lost. This type replaces only the iterable value type.
 */
final class ListingIterableType extends IntersectionType
{
    public function __construct(private readonly Type $listing, private readonly Type $element)
    {
        parent::__construct([$listing, new IterableType(new MixedType(), $element)]);
    }

    public function getIterableValueType(): Type
    {
        return $this->element;
    }

    public function traverse(callable $cb): Type
    {
        $listing = $cb($this->listing);
        $element = $cb($this->element);

        return $listing === $this->listing && $element === $this->element ? $this : new self($listing, $element);
    }

    public function traverseSimultaneously(Type $right, callable $cb): Type
    {
        $listing = $this->listing->traverseSimultaneously($right instanceof self ? $right->listing : $right, $cb);
        $element = $cb($this->element, $right->getIterableValueType());

        return $listing === $this->listing && $element === $this->element ? $this : new self($listing, $element);
    }
}
