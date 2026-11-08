<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

function relationFallbacks(Product $product): void
{
    $item = $product->getContent()[0];
    assertType('mixed', $item['invalid']->getData());
    assertType('array<int, Pimcore\\Model\\DataObject\\Concrete>', $item['missing']->getData());
    assertType('array<int, mixed>', $item['noKinds']->getData());
    assertType('mixed', $item['emptyName']->getData());
    foreach ($product->getReferences() as $metadata) {
        assertType('Pimcore\\Model\\Element\\ElementInterface|null', $metadata->getElement());
    }
}
