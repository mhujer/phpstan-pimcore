<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product;

function languageGetters(Product $product, string $dynamic): void
{
    $product->getTitle('en');
    $product->getTitle('de_DE');
    $product->getTitle('DE');
    $product->getTitle('de-DE');
    $product->getTitle(null);
    $product->getTitle('');
    $product->getTitle($dynamic);
}
