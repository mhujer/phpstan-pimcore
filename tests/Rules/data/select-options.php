<?php

declare(strict_types=1);

namespace SelectOptions;

use App\Model\Product as ProductOverride;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;

function writes(Product $product): void
{
    $product->setColor('blue');
    $product->setColor('red');
    $product->setColor(null);
    $product->setColor('');
}

/** @param list<string> $unknown */
function moreWrites(Product $product, bool $condition, string $unknownString, array $unknown): void
{
    $product->set('color', 'blue');
    $product->set(fieldName: 'color', value: 'blue');
    $product->setColor(color: 'blue');
    $product->setSeason('autumn', 'en');
    $product->getLocalizedfields()->setLocalizedValue('season', 'autumn', 'en');
    $product->setColors(['red', 'blue']);
    $product->set('colors', ['blue']);
    $product->setColor($condition ? 'red' : 'blue');
    $product->setColors($condition ? ['green'] : ['blue']);
    $product->setColor('reed');
    $product->setColor($unknownString);
    $product->setColors($unknown);
    $product->setColors([]);
    $product->setColors(null);
    $product->setColors(['', 'green', null, 7]);
    $product->setSize('blue');
    $product->setSizes(['blue']);
    $product->setValues(['color' => 'blue']);
    Product::create(['color' => 'blue']);
}

function ownerContexts(
    Dimensions $dimensions,
    StockEntry $entry,
    Product|Category $union,
    ProductOverride $override,
): void {
    $dimensions->setUnit('inch');
    $dimensions->set('unit', 'inch');
    $entry->setWarehouse('missing');
    $entry->set('warehouse', 'missing');
    $union->set('color', 'blue');
    $override->setColor('blue');
}

function edgeDefinitions(Product $product, Category $category): void
{
    $category->setCode('1');
    $category->setCode('2');
    $category->setName('apricott');
    $product->setSku('blue');
    $product->setColor(42);
    $product->setColors([42]);
}
