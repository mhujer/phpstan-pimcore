<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Product;

/**
 * @param ObjectMetadata<Product, 'note'|'data'|'object'|'columns'> $object
 * @param ElementMetadata<Product, 'note'|'element'> $element
 */
function metadataColumns(ObjectMetadata $object, ElementMetadata $element): void
{
    $object->getNote();
    $object->setnOtE([]);
    $element->getNote();
    $element->setNote(new stdClass());
    $object->getMissing();
    $element->setMissing(1);
    $object->setNote();
    $object->setData('invalid');
    $object->setObject('invalid');
    $element->setElement('invalid');
}

function metadataFromDefinitions(Product $product): void
{
    foreach ($product->getAccessories() as $object) {
        $object->getUnknown();
        $object->setUnknown(1);
        $object->getNote();
    }
    foreach ($product->getReferences() as $element) {
        $element->getUnknown();
        $element->setNote('valid');
    }
    $product->setAccessories($product->getAccessories());
    $product->setReferences($product->getReferences());
}
