<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

final class SetValueOnlyListing extends DataObject\Listing
{
    public function setValue(string $key, mixed $value, bool $ignoreEmptyValues = false): static
    {
        unset($key, $value, $ignoreEmptyValues);

        return $this;
    }
}
