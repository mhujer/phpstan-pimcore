<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldReceivers;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData as FieldcollectionData;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Objectbrick;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData as BrickData;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Product\Attributes;

function baseClassesAndUnknownReceiversAreNotChecked(
    Concrete $c,
    AbstractObject $o,
    Objectbrick $container,
    BrickData $brick,
    FieldcollectionData $item,
    mixed $unknown,
): void {
    $c->get('nosuch');
    $c->getValueForFieldName('nosuch');
    $o->get('nosuch');
    $container->get('nosuch');
    $brick->get('nosuch');
    $item->get('nosuch');
    $unknown->get('nosuch');
}

function generatedClassesAreChecked(Attributes $container, Dimensions $brick, PriceTier $item): void
{
    $container->get('Dimensions');
    $container->get('nosuch');
    $brick->get('width');
    $brick->getValueForFieldName('width');
    $brick->get('nosuch');
    $brick->getValueForFieldName('nosuch');
    $item->get('minQuantity');
    $item->get('nosuch');
}

function unionOfGeneratedClassesNeedsTheFieldInEach(Product|Category $object, ?Product $maybe): void
{
    $object->get('id');
    $object->get('sku');
    $maybe?->get('nosuch');
    $maybe?->GET('nosuch');
}
