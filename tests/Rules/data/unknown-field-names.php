<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldNames;

use Pimcore\Model\DataObject\Product;

/**
 * @param 'sku'|'nosuch'|'other' $constantUnion
 * @param 'title'|'sku' $localizedOrNot
 */
function everyConstantNameIsChecked(Product $p, string $name, string $constantUnion, string $localizedOrNot): void
{
    $p->get($name);
    $p->getValueForFieldName($name);
    $p->get(Product::FIELD_SKU);
    $p->get($constantUnion);
    $p->getValueForFieldName($localizedOrNot);
}

function namedArguments(Product $p): void
{
    $p->get(language: 'en', fieldName: 'title');
    $p->get(language: 'en', fieldName: 'nosuch');
    $p->getValueForFieldName(key: 'nosuch');
}
