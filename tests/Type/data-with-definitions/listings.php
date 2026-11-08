<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DefinitionListings;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

/** @param Concrete<Product> $products */
function genericListings(Concrete $products, Product\Listing $generated, DataObject\Listing $objects): void
{
    foreach ($products as $key => $product) {
        assertType('int', $key);
        assertType('Pimcore\Model\DataObject\Product', $product);
    }
    assertType('list<Pimcore\Model\DataObject\Product>', $products->load());
    assertType('Pimcore\Model\DataObject\Product|false', $generated->current());
    assertType('list<Pimcore\Model\DataObject\Product>', $generated->getItems(0, 10));
    assertType('list<int>', $generated->loadIdList());
    assertType('Pimcore\Model\DataObject\Listing\Concrete\Dao', $generated->getDao());
    assertType('Doctrine\DBAL\Query\QueryBuilder', $generated->getQueryBuilder());
    assertType('int', $generated->getTotalCount());
    assertType('int', $generated->getCount());
    assertType('list<Pimcore\Model\DataObject>', $objects->load());
    assertType('Pimcore\Model\DataObject|false', $objects->current());
    $objects->onCreateQueryBuilder(null);
    $objects->addQueryBuilderProcessor(static function (): void {});
    $objects->discardQueryBuilderProcessors();
}
