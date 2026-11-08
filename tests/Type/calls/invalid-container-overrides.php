<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\InvalidContainerOverrides;

use App\Model\Product as UnregisteredProduct;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

assertType('Pimcore\Model\DataObject\Product|null', Product::getById(1));
assertType('Pimcore\Model\DataObject\Product|null', Product::getBySku('a', 1));
assertType('Pimcore\Model\DataObject\Category|null', Category::getById(1));
assertType('list<Pimcore\Model\DataObject\Product>', Product::getList()->load());
assertType('null', UnregisteredProduct::getById(1));
assertType('null', UnregisteredProduct::getBySku('a', 1));

function relations(Product $product): void
{
    assertType('Pimcore\Model\DataObject\Category|null', $product->getMainCategory());
    foreach ($product->getAccessories() as $item) {
        assertType('Pimcore\Model\DataObject\Product|null', $item->getObject());
    }
}
