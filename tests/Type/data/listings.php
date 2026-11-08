<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function listingLoad(Product\Listing $list): void
{
    assertType('list<Pimcore\Model\DataObject\Product>', $list->load());
}

function listingGetDataAndObjects(Product\Listing $list): void
{
    assertType('list<Pimcore\Model\DataObject\Product>', $list->getData());
    assertType('list<Pimcore\Model\DataObject\Product>', $list->getObjects());
}

function listingGetItems(Product\Listing $list): void
{
    assertType('list<Pimcore\Model\DataObject\Product>', $list->getItems(0, 10));
}

function listingLoadIdList(Product\Listing $list): void
{
    assertType('list<int>', $list->loadIdList());
}

function otherClassListing(Category\Listing $list): void
{
    assertType('list<Pimcore\Model\DataObject\Category>', $list->load());
    assertType('list<Pimcore\Model\DataObject\Category>', $list->getItems(0, 10));
    assertType('list<Pimcore\Model\DataObject\Category>', Category::getList()->getData());
    assertType('list<Pimcore\Model\DataObject\Category>', Category::getByName('a')->getObjects());
}

function genericListings(DataObject\Listing $list, DataObject\Listing\Concrete $concreteList): void
{
    assertType('list<Pimcore\Model\DataObject>', $list->load());
    assertType('list<Pimcore\Model\DataObject>', $list->getItems(0, 10));
    assertType('list<int>', $list->loadIdList());
    assertType('list<Pimcore\Model\DataObject\Concrete>', $concreteList->getObjects());
}

function listingFluentApiIsUnchanged(Product\Listing $list): void
{
    assertType('Pimcore\Model\DataObject\Product|false', $list->current());
    assertType('int', $list->getTotalCount());
    assertType('int<0, max>', $list->count());
    assertType('Pimcore\Model\DataObject\Product\Listing', $list->filterBySku('a'));
    assertType('Pimcore\Model\DataObject\Product\Listing', $list->setCondition('sku = ?', ['a']));
    assertType('Pimcore\Model\DataObject\Product\Listing', $list->setLimit(10)->setOffset(0)->setOrderKey('sku'));
}

function listingForeachIsUnchanged(Product\Listing $list): void
{
    // Known limitation: foreach uses current() and key(), which no extension can reach.
    foreach ($list as $key => $product) {
        assertType('Pimcore\Model\DataObject\Product|false', $product);
        assertType('int|string|null', $key);
    }
}
