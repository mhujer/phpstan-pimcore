<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Product;

final class SanitizingKeyAsset extends Asset
{
    public function setKey(string $key): static
    {
        return parent::setKey(str_replace('/', '-', $key));
    }
}

final class CustomValuesAsset extends Asset
{
    /** @param array<string, mixed> $data */
    public function setValues(array $data = [], bool $ignoreEmptyValues = false): static
    {
        unset($data['key']);

        return parent::setValues($data, $ignoreEmptyValues);
    }
}

final class CustomCreateProduct extends Product
{
    /** @param array<string, mixed> $values */
    public static function create(array $values = []): static
    {
        unset($values['key']);

        return parent::create($values);
    }
}

function customElementKeys(SanitizingKeyAsset $asset, CustomValuesAsset $values): void
{
    $asset->setKey('bad/name');
    $asset->setValues(['key' => 'bad/name']);
    $values->setValues(['key' => 'bad/name']);
    CustomCreateProduct::create(['key' => 'bad/name']);
    $asset->setKey(...['bad/name']);
    $asset->setKey(...);
}
