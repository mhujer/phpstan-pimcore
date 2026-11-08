<?php

declare(strict_types=1);

namespace ValuesCreate;

use Pimcore\Model\DataObject\Product;

Product::create(['skuu' => 'x']);
Product::create(['sku' => 'x', 'SKU' => 'x', 'name' => 'x']);
Product::create(['value' => 'x']);
Product::create(['o_key' => 'x']);
Product::create([7 => 'x']);
Product::create(['completelyUnknown' => 'x']);

/** @param 'sku'|'skuu' $key */
function unionKey(string $key): void
{
    Product::create([$key => 'x']);
}

function dynamicKey(string $key): void
{
    Product::create([$key => 'x']);
}
Product::create(['s' => 'x']);
Product::create(['id' => 1]);
Product::create(values: ['sku' => 'x']);
$values = ['completelyUnknown' => 'x'];
Product::create($values);
Product::create(...);
Product::create([...$values]);
Product::create(['x']);
