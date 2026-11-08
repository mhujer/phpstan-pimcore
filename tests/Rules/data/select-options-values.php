<?php

declare(strict_types=1);

namespace SelectOptionsValues;

use App\Model\CustomCreateProduct;
use App\Model\CustomValuesProduct;
use App\Model\Product as ProductOverride;
use Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;

/** @param array<string, mixed> $unknownValues */
function writes(Product $product, ProductOverride $override, Dimensions $dimensions, StockEntry $entry, string $unknown, array $unknownValues): void
{
    Product::create(['color' => 'blue']);
    Product::create(values: ['colors' => ['red', 'blue']]);
    $product->setValues(values: ['season' => 'autumn']);
    $product->setValue('color', 'blue');
    $product->setValue(value: 'blue', key: 'color');
    $override->setValues(['color' => 'blue']);
    ProductOverride::create(['color' => 'blue']);
    $dimensions->setValues(['unit' => 'inch']);
    $entry->setValue('warehouse', 'missing');

    $product->setValues(['color' => 'red', 'colors' => [], 'season' => null]);
    $product->setValues(['color' => '', 'colors' => ['', 'green']]);
    $product->setValues(['size' => 'blue', 'sizes' => ['blue'], 'sku' => 'blue']);
    $product->setValues(['color' => $unknown, $unknown => 'blue']);
    $product->setValues($unknownValues);
    $product->setValues([...$unknownValues]);
    $product->setValue($unknown, 'blue');
    $product->setValues(...$unknownValues);
    $product->setValues(...);
    Product::create(...);
}

function customValues(CustomValuesProduct $product): void
{
    $product->setValues(['color' => 'blue']);
    $product->setValue('color', 'blue');
    CustomValuesProduct::create(['color' => 'blue']);
    CustomCreateProduct::create(['color' => 'blue']);
}
