<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldValueForFieldName;

use Pimcore\Model\DataObject\Product;

function getValueForFieldNameMatchesThePropertyExactly(Product $p): void
{
    $p->getValueForFieldName('sku');
    $p->getValueForFieldName('id');
    $p->getValueForFieldName('localizedfields');
    $p->getValueForFieldName('SKU');
    $p->getValueForFieldName('nosuch');
    $p->getValueForFieldName('Sku');
    $p->getValueForFieldName('Name');
}

function localizedFieldIsAlwaysNull(Product $p): void
{
    $p->getValueForFieldName('title');
}
