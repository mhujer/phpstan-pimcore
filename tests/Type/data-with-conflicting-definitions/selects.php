<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithConflictingDefinitions;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function conflictsKeepTheGeneratedType(Product $p): void
{
    assertType('float|null', $p->getPrice());
    assertType('string|null', $p->getClassName());
    assertType('string|null', $p->getColor());
}
