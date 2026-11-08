<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function findersWithoutLimit(): void
{
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a'));
}

function findersWithLimitOne(): void
{
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', '1'));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', true));
}

function findersWithOtherConstantLimit(): void
{
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', 10));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', null));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', 0));
}

function findersWithNonConstantLimit(int $limit, ?int $nullableLimit): void
{
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', Product::getBySku('a', $limit));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', Product::getBySku('a', $nullableLimit));
}

/**
 * @param array{limit: int} $limitConfig
 * @param array<string, mixed> $config
 */
function findersWithArrayLimit(array $limitConfig, array $config): void
{
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', ['limit' => 1]));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', ['limit' => '1', 'offset' => 5]));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', ['limit' => 10]));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', ['offset' => 5]));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', Product::getBySku('a', $limitConfig));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', Product::getBySku('a', $config));
}

function findersWithNamedLimit(): void
{
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku(value: 'a', limit: 1));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', offset: 5, limit: 1));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getBySku('a', offset: 5));
}

function localizedFinders(): void
{
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getByTitle('a', 'en'));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getByTitle('a', 'en', 1));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getByTitle('a', 'en', 10));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getByTitle('a', limit: 1));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getByLocalizedfields('title', 'a', 'en', 1));
}

function systemColumnFinder(): void
{
    assertType('Pimcore\Model\DataObject\Product|null', Product::getByKey('a', 1));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getByKey('a'));
}

function otherClassFinder(): void
{
    assertType('Pimcore\Model\DataObject\Category|null', Category::getByName('a', 1));
    assertType('Pimcore\Model\DataObject\Category\Listing', Category::getByName('a'));
}

function nonFinderGetters(): void
{
    assertType('Pimcore\Model\DataObject\Product|null', Product::getById(1));
    assertType('Pimcore\Model\DataObject\Product|null', Product::getByPath('/products/a'));
    assertType('Pimcore\Model\DataObject|null', DataObject::getById(1));
    assertType('Pimcore\Model\DataObject\Concrete|null', Concrete::getById(1));
    assertType('Pimcore\Model\DataObject\Product', Product::create());
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getList());
    assertType('Pimcore\Model\DataObject\Category\Listing', Category::getList());
}

function undeclaredFinders(): void
{
    // No field "foo": Pimcore throws at runtime.
    assertType('*ERROR*', Product::getByFoo('a', 1));
    // Wrong case works at runtime (Pimcore matches with /i), but it stays reported.
    assertType('*ERROR*', Product::getBysku('a', 1));
}

/**
 * @param list<mixed> $arguments
 */
function findersWithUnpackedArguments(array $arguments): void
{
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', Product::getBySku(...$arguments));
}
