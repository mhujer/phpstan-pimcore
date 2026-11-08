<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithConflictingDefinitions\Line2026;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function staleAllowedTypes(Product $p): void
{
    // The generated item types win.
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->getVariants());
}
