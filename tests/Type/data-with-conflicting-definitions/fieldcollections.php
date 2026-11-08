<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithConflictingDefinitions;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function fieldcollectionDefinitionOnAnotherFieldType(Product $p): void
{
    assertType('Pimcore\Model\Asset\Image|null', $p->getImage());
}
