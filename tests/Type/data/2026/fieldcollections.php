<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data\Line2026;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function fieldcollectionGetterWithoutClassDefinitions(Product $p): void
{
    // 2026.x declares the item types itself
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->getVariants());
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->get('variants'));
}
