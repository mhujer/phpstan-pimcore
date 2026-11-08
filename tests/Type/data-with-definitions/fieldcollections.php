<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithDefinitions;

use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function fieldcollections(Product $p): void
{
    // Same on both lines: on 12.3 the item types come from the class definition (allowedTypes).
    $variants = $p->getVariants();
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $variants);
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->get('variants'));
    if ($variants === null) {
        return;
    }

    // array, not list: setItems() stores any array
    assertType('array<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>', $variants->getItems());
    assertType('Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry|null', $variants->get(0));

    foreach ($variants as $key => $item) {
        assertType('int', $key);
        assertType('Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry', $item);
        if (!($item instanceof PriceTier)) {
            continue;
        }

        assertType('float|null', $item->getPrice());
    }
}
