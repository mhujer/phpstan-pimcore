<?php

declare(strict_types=1);

namespace SelectOptions;

use Pimcore\Model\DataObject\Product;

function writes(Product $product): void
{
    $product->setColor('blue');
    $product->setColor('red');
    $product->setColor(null);
    $product->setColor('');
}
