<?php

declare(strict_types=1);

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;

function listingObjectTypes(Product $product, Product\Listing $list): void
{
    $list->setObjectTypes(['variants']);
    $product->getChildren(['variants']);
    $product->hasChildren(['variants']);
    $product->getSiblings(['variants']);
    $product->hasSiblings(objectTypes: ['variants']);
    $list->setObjectTypes([DataObject::OBJECT_TYPE_OBJECT, DataObject::OBJECT_TYPE_VARIANT, DataObject::OBJECT_TYPE_FOLDER]);
    $product->getChildren([DataObject::OBJECT_TYPE_FOLDER]);
    Product::getBySku('test', objectTypes: ['folder']);
    Product::getBySku('test', null, 0, ['variant']);
    Product::getBySku('test', null, 0, ['invalid']);
    Product::getList(['objectTypes' => ['variants']]);
    $list->setValues(['objectTypes' => ['variants']]);
    $product->setChildren([]);
}
