<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Calls;

use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry;
use Pimcore\Model\DataObject\Product;

/**
 * @param Fieldcollection<PriceTier> $tiers
 */
function addFieldcollectionItems(Fieldcollection $tiers, Product $product, AbstractData $other): void
{
    $tiers->add(new PriceTier());
    $tiers->add(new StockEntry());

    $variants = $product->getVariants();
    if ($variants === null) {
        return;
    }

    $variants->add(new PriceTier());
    $variants->add(new StockEntry());
    $variants->add($other);
}

/**
 * @param Fieldcollection<PriceTier> $tiers
 */
function setFieldcollectionItems(Fieldcollection $tiers, Product $product, AbstractData $other): void
{
    $tiers->setItems(['price' => new PriceTier()]);
    $tiers->setItems([]);
    $tiers->setItems([new StockEntry()]);

    $variants = $product->getVariants();
    if ($variants === null) {
        return;
    }

    $variants->setItems(['price' => new PriceTier(), 4 => new StockEntry()]);
    $variants->setItems([]);
    $variants->setItems([new PriceTier(), $other]);
}

/**
 * @param Fieldcollection<PriceTier> $tiers
 */
function acceptPriceTiers(Fieldcollection $tiers): void
{
    $tiers->add(new PriceTier());
}

function newFieldcollectionsStayAssignable(Product $product): void
{
    acceptPriceTiers(new Fieldcollection());
    $product->setVariants(new Fieldcollection());

    $variants = new Fieldcollection();
    $variants->add(new PriceTier());
    $variants->add(new StockEntry());
    $product->setVariants($variants);
}
