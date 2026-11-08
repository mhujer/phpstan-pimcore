<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldCalculatedValue;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverriddenProduct;
use Pimcore\Model\DataObject\Product;

function getterWithoutPropertyIsACalculatedValue(Product $p): void
{
    $p->getValueForFieldName('sku');
    $p->getValueForFieldName('margin');
    $p->getValueForFieldName('fullPath');
    $p->getValueForFieldName('Margin');
}

function calculatedValueOfClassOverride(OverriddenProduct $p): void
{
    $p->getValueForFieldName('margin');
    $p->getValueForFieldName('Margin');
}
