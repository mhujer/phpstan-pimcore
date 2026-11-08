<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\ContainerOverrides;

use App\Model\AbstractProduct;
use App\Model\ChildImage;
use App\Model\ChildPage;
use App\Model\ChildProduct;
use App\Model\IntermediateProduct as Intermediate;
use App\Model\NativeProduct;
use App\Model\RegisteredProduct;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use function PHPStan\Testing\assertType;

assertType('App\Model\RegisteredProduct|null', Product::getById(1));
assertType('App\Model\RegisteredProduct|null', Product::getByPath('/p'));
assertType('App\Model\RegisteredProduct|null', Product::getBySku('a', 1));
assertType('App\Model\RegisteredProduct|null', Intermediate::getBySku('a', 1));
assertType('App\Model\RegisteredProduct|null', RegisteredProduct::getBySku('a', 1));
assertType('list<App\Model\RegisteredProduct>', Product::getList()->load());
assertType('list<App\Model\RegisteredProduct>', (new Product\Listing())->getData());
assertType('App\Model\RegisteredProduct|false', (new Product\Listing())->current());
assertType('Pimcore\Model\DataObject\Product', new Product());
assertType('Pimcore\Model\DataObject\Product', Product::create());

function relations(Product $product): void
{
    assertType('App\Model\Category|null', $product->getMainCategory());
    assertType('array<App\Model\Category>', $product->getCategories());
    assertType('App\Model\Category|null', $product->get('mainCategory'));
}

assertType('null', ChildProduct::getById(1));
assertType('null', ChildProduct::getByPath('/p'));
assertType('null', ChildProduct::getBySku('a', 1));
assertType('list<Pimcore\Model\DataObject\Product>', ChildProduct::getList()->load());
assertType('App\Model\ProductListing', Product::getList());
assertType('App\Model\ProductListing', Product::getBySku('a'));
assertType('Pimcore\Model\DataObject\Product\Listing', Product::getList(['class' => 'Other']));
assertType('Pimcore\Model\DataObject\Product\Listing', NativeProduct::getList());
assertType('Pimcore\Model\DataObject\Product|Pimcore\Model\DataObject\Product\Listing|null', NativeProduct::getBySku('a', 1));
assertType('App\Model\Page|null', Page::getById(1));
assertType('App\Model\Image|null', Image::getByPath('/a'));
assertType('Pimcore\Model\Document|null', Document::getById(1));
assertType('App\Model\ChildPage|null', ChildPage::getById(1));
assertType('App\Model\ChildImage|null', ChildImage::getById(1));

function metadata(Product $product): void
{
    foreach ($product->getAccessories() as $item) {
        assertType('App\Model\RegisteredProduct|null', $item->getObject());
        assertType('App\Model\RegisteredProduct|null', $item->getElement());
    }
    foreach ($product->getReferences() as $item) {
        assertType('App\Model\Category|Pimcore\Model\Asset|null', $item->getElement());
    }
}

assertType('App\Model\AbstractProduct|null', AbstractProduct::getById(1));
assertType('Pimcore\Model\DataObject\Product\Listing', AbstractProduct::getList());

assertType('list<App\Model\RegisteredProduct>', (new Product\Listing())->getItems(0, 10));

// Explicit generated fallback survives the override-aware template default during iteration too.
foreach (ChildProduct::getList() as $key => $product) {
    assertType('int', $key);
    assertType('Pimcore\Model\DataObject\Product', $product);
}

assertType('list<Pimcore\Model\DataObject\Product>', ChildProduct::getList()->filterBySku('a')->load());
assertType('Pimcore\Model\DataObject\Product|false', ChildProduct::getBySku('a')->current());
foreach (ChildProduct::getBySku('a') as $key => $product) {
    assertType('int', $key);
    assertType('Pimcore\Model\DataObject\Product', $product);
}
