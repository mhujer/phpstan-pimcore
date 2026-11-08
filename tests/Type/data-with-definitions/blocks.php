<?php

declare(strict_types=1);

namespace Blocks;

use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

function blocks(Product $product): void
{
    $item = $product->getContent()[0];
    assertType('Pimcore\\Model\\DataObject\\Data\\BlockElement<string|null>', $item['title']);
    assertType('int|null', $item['qty']->getData());
    assertType("''|'green'|'red'|null", $item['color']->getData());
    assertType('Pimcore\\Model\\DataObject\\Category|null', $item['category']->getData());
    assertType('Pimcore\\Model\\DataObject\\Localizedfield|null', $item['localizedfields']->getData());
    assertType('mixed', $item['location']->getData());
    assertType('string|null', $product->get('content')[0]['title']->getData());
    assertType('string|null', $product->getLocalizedContent('en')[0]['title']->getData());
    assertType('bool', isset($item['title']));
}

function childTypes(Product $product, Dimensions $brick, PriceTier $collection): void
{
    assertType('string|null', $brick->getContent()[0]['title']->getData());
    assertType('string|null', $brick->get('content')[0]['title']->getData());
    assertType('string|null', $collection->getContent()[0]['title']->getData());
    assertType('string|null', $collection->get('content')[0]['title']->getData());
    $item = $product->getContent()[0];
    assertType('string|null', $item['text']->getData());
    assertType('string|null', $item['html']->getData());
    assertType('float|null', $item['amount']->getData());
    assertType('bool|null', $item['enabled']->getData());
    assertType('Carbon\\Carbon|null', $item['date']->getData());
    assertType('Carbon\\Carbon|null', $item['datetime']->getData());
    assertType('Pimcore\\Model\\DataObject\\Data\\Link|null', $item['link']->getData());
    assertType('Pimcore\\Model\\Asset\\Image|null', $item['image']->getData());
    assertType("array<int, 'green'|'red'>|null", $item['colors']->getData());
    assertType('array<int, Pimcore\\Model\\DataObject\\Category>', $item['categories']->getData());
    assertType('array<int, Pimcore\\Model\\Asset|Pimcore\\Model\\DataObject\\Concrete|Pimcore\\Model\\Document>', $item['relations']->getData());
    assertType('string|null', $item['provider']->getData());
}
