<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

// Without class definitions, selects keep the getter's declared type.

function selectsWithoutClassDefinitions(Product $p, Dimensions $d): void
{
    assertType('string|null', $p->getColor());
    assertType('array<string>|null', $p->getColors());
    assertType('string|null', $p->get('color'));
    assertType('string|null', $p->getSeason('en'));
    assertType('string|null', $d->getUnit());
}
