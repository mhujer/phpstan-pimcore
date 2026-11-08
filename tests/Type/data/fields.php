<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

// Getters Pimcore already types correctly; the extension must leave them alone.
function scalars(Product $p): void
{
    assertType('string|null', $p->getSku());
    assertType('float|null', $p->getPrice());
    assertType('bool|null', $p->getActive());
    assertType('Carbon\Carbon|null', $p->getReleaseDate());
    assertType('Pimcore\Model\Asset\Image|null', $p->getImage());
}

function localized(Product $p): void
{
    assertType('string|null', $p->getTitle());
    assertType('string|null', $p->getTitle('en'));
    assertType('string|null', $p->getDescription('de'));
}

function relations(Product $p): void
{
    // array, not list: the setter stores any array and the getter returns it unchanged unless hide-unpublished is on
    assertType('Pimcore\Model\DataObject\Category|null', $p->getMainCategory());
    assertType('array<Pimcore\Model\DataObject\Category>', $p->getCategories());
    assertType('array<Pimcore\Model\Asset|Pimcore\Model\DataObject\AbstractObject|Pimcore\Model\Document>', $p->getRelated());
}

function multiselects(Product $p): void
{
    assertType('array<string>|null', $p->getColors());
    assertType('array<string>|null', $p->getSizes());
}

function relationMetadata(Product $p): void
{
    assertType('array<Pimcore\Model\DataObject\Data\ObjectMetadata>', $p->getAccessories());
    assertType('array<Pimcore\Model\DataObject\Data\ElementMetadata>', $p->getReferences());
}

function blocks(Product $p): void
{
    assertType('array<array<Pimcore\Model\DataObject\Data\BlockElement>>|null', $p->getContent());
    assertType('array<array<Pimcore\Model\DataObject\Data\BlockElement>>|null', $p->getLocalizedContent('en'));
}
