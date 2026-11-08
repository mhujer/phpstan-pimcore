<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithConflictingDefinitions\Line123;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function staleAllowedTypes(Product $p): void
{
    // A stale allowedTypes falls back to any fieldcollection item.
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData>|null', $p->getVariants());
}
