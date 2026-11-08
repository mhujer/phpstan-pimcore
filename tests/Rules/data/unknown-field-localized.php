<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldLocalized;

use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Product;
use Throwable;

/** @param 'title'|'nosuch' $name */
function localized(Product $product, Localizedfield $bare, string $name, string $dynamic): void
{
    $fields = $product->getLocalizedfields();
    if ($fields === null) {
        return;
    }
    $fields->getLocalizedValue('title');
    $fields->setLocalizedValue('title', 42);
    $fields->getLocalizedValue('Title');
    $fields->setLocalizedValue(value: 'x', name: 'sku');
    $fields->getLocalizedValue($name);
    $bare->getLocalizedValue('nosuch');
    $bare->setLocalizedValue('nosuch', 'x');
    $fields->getLocalizedValue($dynamic);
    $product->getValueFromParent('SKU');
    try {
        $product->getValueFromParent(key: 'nosuch');
    } catch (Throwable) {
    }
    Product::getByLocalizedfields('title', 'x');
    Product::getByLocalizedfields(value: 'x', field: 'Title');
    Product::getByLocalizedfields($name, 'x');
    Product::getByLocalizedfields($dynamic, 'x');
}
