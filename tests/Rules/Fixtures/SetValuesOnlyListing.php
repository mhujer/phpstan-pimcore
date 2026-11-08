<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

final class SetValuesOnlyListing extends DataObject\Listing
{
    /** @param array<string, mixed> $data */
    public function setValues(array $data = [], bool $ignoreEmptyValues = false): static
    {
        unset($data, $ignoreEmptyValues);

        return $this;
    }
}
