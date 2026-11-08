<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use App\Model\AbstractProduct;
use App\Model\Category;
use App\Model\NativeChildProduct;
use App\Model\NativeProduct;
use App\Model\NotGenerated;
use App\Model\Product;
use App\Model\SpecialProduct;
use App\Model\TaggedChildProduct;
use App\Model\TaggedProduct;
use Pimcore\Model\DataObject\Category as GeneratedCategory;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Product\Listing;
use function PHPStan\Testing\assertType;

function overrideFinder(): void
{
    assertType('App\Model\Product|null', Product::getBySku('a', 1));
    assertType('App\Model\Product|null', Product::getBySku('a', '1'));
    assertType('App\Model\Product|null', Product::getByTitle('a', 'en', 1));
    assertType('list<App\Model\Product>', Product::getBySku('a', ['offset' => 5])->load());
    assertType('list<App\Model\SpecialProduct>', SpecialProduct::getBySku('a', ['limit' => 10])->load());
    assertType('list<App\Model\Product>', Product::getBySku('a', 0)->load());
    assertType('list<App\Model\Product>', Product::getBySku('a', null)->load());
    assertType('App\Model\SpecialProduct|null', SpecialProduct::getBySku('a', ['limit' => 1]));
}

function overrideListings(): void
{
    $list = Product::getBySku('a');
    assertType('list<App\Model\Product>', $list->load());
    assertType('list<App\Model\Product>', $list->getData());
    assertType('list<App\Model\Product>', $list->getObjects());
    assertType('list<App\Model\Product>', $list->getItems(0, 10));
    assertType('App\Model\Product|false', $list->current());
    foreach ($list as $product) {
        assertType('App\Model\Product', $product);
    }
}

function overrideGetListAndChaining(): void
{
    $list = SpecialProduct::getList();
    assertType('list<App\Model\SpecialProduct>', $list->load());
    assertType('list<App\Model\SpecialProduct>', $list->setLimit(10)->setOffset(0)->load());
    assertType('list<App\Model\SpecialProduct>', $list->setCondition('sku = ?', ['a'])->filterBySku('a')->getItems(0, 10));
}

/**
 * @param class-string<SpecialProduct> $class
 */
function overrideClassString(string $class): void
{
    assertType('App\Model\SpecialProduct|null', $class::getBySku('a', limit: true));
    assertType('list<App\Model\SpecialProduct>', $class::getList()->load());
    assertType('list<App\Model\SpecialProduct>', $class::getByTitle('a', limit: 10)->setLimit(2)->getData());
}

function abstractOverride(): void
{
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', AbstractProduct::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product\Listing', AbstractProduct::getList());
}

function overrideListingConfig(): void
{
    assertType('list<App\Model\Product>', Product::getList(config: ['limit' => 10])->load());
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getList(['class' => 'Other']));
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getList(config: ['class' => 'Other', 'limit' => 10]));
    assertType('App\Model\Product|null', Product::getById(1));
    assertType('App\Model\SpecialProduct|null', SpecialProduct::getByPath('/product'));
    assertType('list<int>', Product::getList()->loadIdList());
}

function redeclaredOverrideMethods(): void
{
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', NativeProduct::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', NativeChildProduct::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', TaggedProduct::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', TaggedChildProduct::getBySku('a', 1));
    assertType('Pimcore\Model\DataObject\Product\Listing', NativeProduct::getList());
    assertType('Pimcore\Model\DataObject\Product\Listing', NativeChildProduct::getList());
    assertType('Pimcore\Model\DataObject\Product\Listing', TaggedProduct::getList());
    assertType('Pimcore\Model\DataObject\Product\Listing', TaggedChildProduct::getList());
}

/**
 * @param class-string<Product>|class-string<Category> $class
 */
function overrideClassStringUnion(string $class): void
{
    assertType('App\Model\Category|App\Model\Product|null', $class::getByKey('a', 1));
    assertType('list<App\Model\Category|App\Model\Product>', $class::getList()->load());
    foreach ($class::getByKey('a', 10)->setLimit(2) as $object) {
        assertType('App\Model\Category|App\Model\Product', $object);
    }
}

/**
 * @param class-string<Product>|class-string<Concrete> $class
 */
function mixedClassStringUnion(string $class): void
{
    assertType('Pimcore\Model\DataObject\Listing', $class::getList());
}

/**
 * @param class-string<Product>|class-string<GeneratedCategory> $class
 */
function mixedGeneratedAndOverrideUnion(string $class): void
{
    assertType('Pimcore\Model\DataObject\Category|Pimcore\Model\DataObject\Category\Listing|Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', $class::getByKey('a', 1));
}

/**
 * @param array{limit: int} $config
 */
function unknownOverrideFinderLimit(int $limit, array $config): void
{
    $result = Product::getBySku('a', $limit);
    if ($result instanceof Listing) {
        assertType('list<App\Model\Product>', $result->load());
    } else {
        assertType('App\Model\Product|null', $result);
    }

    $result = Product::getBySku('a', $config);
    if ($result instanceof Listing) {
        assertType('list<App\Model\Product>', $result->load());
    } else {
        assertType('App\Model\Product|null', $result);
    }
}

/**
 * @param array<string, mixed> $config
 */
function unknownListingConfig(array $config): void
{
    assertType('Pimcore\Model\DataObject\Product\Listing', Product::getList($config));
    assertType('Pimcore\Model\DataObject\Listing', NotGenerated::getList());
    assertType('App\Model\SpecialProduct|false', SpecialProduct::getList()->setLimit(1)->current());
    foreach (SpecialProduct::getList() as $object) {
        assertType('App\Model\SpecialProduct', $object);
    }
}
