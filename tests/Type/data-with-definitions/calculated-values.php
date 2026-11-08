<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

function calculatedValues(Product $product, Category $category): void
{
    assertType('string|null', $product->getMargin());
    assertType('string|null', $category->getLabel());
    assertType('mixed', $product->getComputedPrice());
    assertType('string|null', $product->get('margin'));
}
