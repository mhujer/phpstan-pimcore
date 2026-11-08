<?php

declare(strict_types=1);

use App\Model\ChildProduct;

use Pimcore\Model\DataObject\Product;

function constructions(): void
{
    $exact = Product::class;
    new $exact();
    $exact::create();
}

$child = ChildProduct::class;
$child::getById(1);
