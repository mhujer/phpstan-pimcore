<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use stdClass;
use function PHPStan\Testing\assertType;

function bareMetadata(ObjectMetadata $object, ElementMetadata $element, Product $product): void
{
    assertType('mixed', $object->getAnything());
    assertType('mixed', $element->getAnything());
    assertType('null', $object->setAnything(new stdClass()));
    assertType('null', $element->setAnything([]));
    assertType('Pimcore\\Model\\DataObject\\Concrete|null', $object->getElement());
    assertType('Pimcore\\Model\\Element\\ElementInterface|null', $element->getElement());
    foreach ($product->getAccessories() as $item) {
        assertType('mixed', $item->getUnknown());
        assertType('Pimcore\\Model\\DataObject\\Concrete|null', $item->getElement());
    }
    foreach ($product->getReferences() as $item) {
        assertType('mixed', $item->getUnknown());
        assertType('Pimcore\\Model\\Element\\ElementInterface|null', $item->getElement());
    }
    assertType('mixed', (new ObjectMetadata('accessories'))->getUnknown());
    assertType('mixed', (new ElementMetadata())->getUnknown());
}

/**
 * @param ObjectMetadata<Product, 'note'|'data'> $object
 * @param ElementMetadata<Product, 'note'> $element
 */
function annotatedMetadata(ObjectMetadata $object, ElementMetadata $element): void
{
    assertType('mixed', $object->getnOtE());
    assertType('mixed', $element->getNote());
    assertType('array', $object->getData());
    assertType('Pimcore\\Model\\DataObject\\Product|null', $object->getObject());
    assertType('Pimcore\\Model\\DataObject\\Product|null', $element->getElement());
}

function bareMetadataInEveryGeneratedOwner(
    Product $product,
    Dimensions $brick,
    PriceTier $collection,
): void {
    foreach ($product->getLocalizedAccessories('en') as $item) {
        assertType('Pimcore\\Model\\DataObject\\Concrete|null', $item->getElement());
        assertType('mixed', $item->getUnknown());
    }
    foreach ($brick->get('accessories') as $item) {
        assertType('Pimcore\\Model\\DataObject\\Concrete|null', $item->getElement());
        assertType('mixed', $item->getUnknown());
    }
    foreach ($collection->getReferences() as $item) {
        assertType('Pimcore\\Model\\Element\\ElementInterface|null', $item->getElement());
        assertType('mixed', $item->getUnknown());
    }
}
