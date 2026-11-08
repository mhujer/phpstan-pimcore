<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithDefinitions;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverriddenProduct;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\RedeclaringOverriddenProduct;

use function PHPStan\Testing\assertType;

function fieldcollectionsOnClassOverride(OverriddenProduct $p): void
{
    // A getter inherited from the generated class narrows the same as on the generated class.
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->getVariants());
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->get('variants'));
}

function fieldcollectionsOnRedeclaringClassOverride(RedeclaringOverriddenProduct $p): void
{
    // The getter still belongs to the generated class's field.
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null', $p->getVariants());
}
