<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product;

function listingOrder(Product\Listing $list, string $dynamic, bool $choose): void
{
    $list->setOrder('desc');
    $list->setOrder('random');
    $list->setOrder(' asc');
    $list->setOrder(['ASC', 'foo']);
    $list->setOrder('');
    $list->setOrder($choose ? 'ASC' : 'bad');
    $list->setOrder($dynamic);
    Product::getList(['order' => 'random']);
    $list->setValues(['order' => 'random']);
}
