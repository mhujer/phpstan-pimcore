<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithDefinitions;

use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;

use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

function relationMetadata(Product $product): void
{
    foreach ($product->getAccessories() as $item) {
        assertType('Pimcore\\Model\\DataObject\\Product|null', $item->getElement());
        assertType('Pimcore\\Model\\DataObject\\Product|null', $item->getObject());
        assertType('mixed', $item->getNote());
        assertType('mixed', $item->getnOtE());
        assertType('null', $item->setQty(3));
        assertType('array', $item->getData());
    }
    $product->setAccessories($product->getAccessories());
    $product->setReferences($product->getReferences());
    foreach ($product->get('references') as $item) {
        assertType('Pimcore\\Model\\Asset|Pimcore\\Model\\DataObject\\Category|null', $item->getElement());
        assertType('mixed', $item->getNote());
    }
}

function metadataInEveryGeneratedOwner(
    Product $product,
    Dimensions $brick,
    PriceTier $collection,
): void {
    foreach ($product->getLocalizedAccessories('en') as $item) {
        assertType('Pimcore\\Model\\DataObject\\Product|null', $item->getElement());
        assertType('mixed', $item->getNote());
    }
    foreach ($product->get('localizedAccessories', 'en') as $item) {
        assertType('Pimcore\\Model\\DataObject\\Product|null', $item->getElement());
    }
    $localized = $product->getLocalizedfields();
    if ($localized !== null) {
        foreach ($localized->getLocalizedValue('localizedAccessories', 'en') as $item) {
            assertType('Pimcore\\Model\\DataObject\\Product|null', $item->getElement());
        }
    }
    foreach ($brick->getAccessories() as $item) {
        assertType('Pimcore\\Model\\DataObject\\Concrete|null', $item->getElement());
        assertType('mixed', $item->getNote());
    }
    foreach ($collection->get('references') as $item) {
        assertType('Pimcore\\Model\\DataObject\\Concrete|Pimcore\\Model\\Document|null', $item->getElement());
        assertType('mixed', $item->getNote());
    }
}
