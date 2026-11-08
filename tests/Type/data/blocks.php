<?php

declare(strict_types=1);

namespace BlocksWithoutDefinitions;

use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

function blocks(Product $product, BlockElement $bare, Dimensions $brick, PriceTier $collection): void
{
    assertType('array<array<Pimcore\\Model\\DataObject\\Data\\BlockElement>>|null', $product->getContent());
    assertType('mixed', $product->getContent()[0]['title']->getData());
    assertType('mixed', $product->getLocalizedContent('en')[0]['title']->getData());
    assertType('mixed', $bare->getData());
    assertType('mixed', $brick->getContent()[0]['title']->getData());
    assertType('mixed', $collection->getContent()[0]['title']->getData());
    assertType('mixed', $product->get('content')[0]['title']->getData());
    assertType('string', $bare->getName());
    assertType('string', $bare->getType());
    $element = new BlockElement('title', 'input', 'hello');
    assertType("Pimcore\\Model\\DataObject\\Data\\BlockElement<'hello'>", $element);
    assertType("'hello'", $element->getData());
    $element->setData(123);
}
