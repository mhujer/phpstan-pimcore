<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

final class ConfigListing extends DataObject\Listing
{
    /** @param array<string, mixed> $data */
    public function setValues(array $data = [], bool $ignoreEmptyValues = false): static
    {
        return parent::setValues($data, $ignoreEmptyValues);
    }

    public function setValue(string $key, mixed $value, bool $ignoreEmptyValues = false): static
    {
        return parent::setValue($key, $value, $ignoreEmptyValues);
    }
}
