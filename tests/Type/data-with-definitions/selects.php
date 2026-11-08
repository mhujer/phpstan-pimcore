<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithDefinitions;

use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function selectWithStaticOptions(Product $p): void
{
    // static options: red, green
    assertType("''|'green'|'red'|null", $p->getColor());
}

function selectThroughGenericGetter(Product $p): void
{
    assertType("''|'green'|'red'|null", $p->get('color'));
    assertType("''|'green'|'red'|null", $p->getValueForFieldName('color'));
}

function localizedSelect(Product $p): void
{
    assertType("''|'summer'|'winter'|null", $p->getSeason());
    assertType("''|'summer'|'winter'|null", $p->getSeason('en'));
}

function selectInObjectbrick(Dimensions $d): void
{
    assertType("''|'cm'|'mm'|null", $d->getUnit());
}

function selectWithOptionsProvider(Product $p): void
{
    assertType('string|null', $p->getSize());
}

function otherFieldsKeepTheirDeclaredType(Product $p, Category $c): void
{
    assertType('string|null', $p->getSku());
    assertType('float|null', $p->getPrice());
    assertType('string|null', $c->getName());
}

function unionOfClasses(Product|Dimensions $o): void
{
    assertType("''|'cm'|'green'|'mm'|'red'|null", $o instanceof Product ? $o->getColor() : $o->getUnit());
}

function multiselectWithStaticOptions(Product $p): void
{
    assertType("array<'green'|'red'>|null", $p->getColors());
    assertType("array<'green'|'red'>|null", $p->get('colors'));
    assertType('array<string>|null', $p->getSizes());
}
