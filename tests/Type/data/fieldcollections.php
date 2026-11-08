<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry;

use function PHPStan\Testing\assertType;

/**
 * @param Fieldcollection<PriceTier|StockEntry> $variants
 */
function foreachYieldsTheItemsWithAnIntKey(Fieldcollection $variants): void
{
    foreach ($variants as $key => $item) {
        assertType('int', $key);
        assertType('Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry', $item);
    }
}

function foreachOverABareFieldcollectionYieldsAbstractData(Fieldcollection $collection): void
{
    foreach ($collection as $item) {
        assertType(AbstractData::class, $item);
    }
}

/**
 * @param Fieldcollection<PriceTier|StockEntry> $variants
 */
function itemsStayAnArray(Fieldcollection $variants): void
{
    // array, not list: setItems() stores any array
    assertType('array<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>', $variants->getItems());
    assertType('Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry|null', $variants->get(0));
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>', $variants->setItems(['price' => new PriceTier()]));
}

function pimcoreMethodTagsAreKept(Fieldcollection $collection, Concrete $object, PriceTier $item): void
{
    assertType('Pimcore\Model\DataObject\Fieldcollection\Dao', $collection->getDao());
    // The stub also declares these classes, without touching their PHPDoc.
    assertType('Pimcore\Model\DataObject\Concrete\Dao', $object->getDao());
    assertType('Pimcore\Model\Version|null', $object->getLatestVersion());
    assertType('Pimcore\Model\DataObject\Fieldcollection\Data\Dao', $item->getDao());
}
