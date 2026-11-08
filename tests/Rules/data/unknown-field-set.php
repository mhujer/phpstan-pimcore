<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldSet;

use Pimcore\Model\DataObject\Product;

/**
 * @param 'sku'|'title' $name
 * @param array<string> $values
 */
function setters(Product $product, string $name, string|int $maybe, mixed $unknown, array $values): void
{
    $product->set('sku', 'ok');
    $product->set('SKU', null);
    $product->set('sku', $maybe);
    $product->set($name, 42);
    $product->set('nosuch', 'x');
    $product->set('margin', 1);
    $product->set(value: 42, fieldName: 'sku');
    $product->set('sku', $unknown);
    $product->set('sku', ...$values);
}
