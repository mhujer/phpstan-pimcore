<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product;

function listingKey(Product\Listing $list, bool $quote): void
{
    $list->setOrderKey('RAND()');
    $list->setOrderKey('RAND()', true);
    $list->setOrderKey('RAND()', false);
    $list->setOrderKey('name DESC');
    $list->setOrderKey('o.name');
    $list->setOrderKey(['name', 'id']);
    $list->setOrderKey('`name`');
    $list->setGroupBy('a, b');
    $list->setGroupBy('DATE(created)');
    $list->setGroupBy('`name`');
    $list->setOrderKey('RAND()', $quote);
    $list->setOrderKey(quote: false, orderKey: 'RAND()');
    Product::getList(['orderKey' => 'RAND()']);
    $list->setValues(['groupBy' => 'DATE(created)']);
}
