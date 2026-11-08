<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldSetReceivers;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Product\Attributes;

/** @param 'sku'|'nosuch'|'other' $name */
function setters(Attributes $container, Dimensions $brick, PriceTier $item, ?Product $product, Concrete $base, mixed $unknown, string $name): void
{
    $container->set('Dimensions', null);
    $container->set('nosuch', null);
    $brick->set('width', 1.0);
    $brick->set('nosuch', null);
    $item->set('minQuantity', 1.0);
    $item->set('nosuch', null);
    $product?->SET('nosuch', null);
    $base->set('nosuch', null);
    $unknown->set('nosuch', null);
    $product?->set($name, 42);
    $product?->getValueFromParent($name);
}
