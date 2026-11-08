<?php

declare(strict_types=1);

use App\Model\NativeProduct;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Product\Listing;

Product::getById(1);
Product::getByPath('/p');
Category::getById(1);
Product::getBySku('a', 1);
Product::getBySku('a');
Product::getList();

function results(Product $product, Listing $list): void
{
    $product->getSku();
    $product->getMainCategory();
    $product->getCategories();
    $product->get('mainCategory');
    $list->load();
    $list->loadIdList();
    $list->current();
    $list->setLimit(10);
    foreach ($list as $item) {
        echo $item->getId();
    }
}

new Product();
Product::create();
NativeProduct::getList();
Product::getList(['class' => 'Other']);

function moreResults(Product $product, Listing $list): void
{
    $product->getAccessories();
    $list->getData();
    $list->getObjects();
    $list->getItems(0, 10);
}

(new Listing())->load();
