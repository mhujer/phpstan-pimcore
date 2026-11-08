<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\RedeclaringOverriddenProduct;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData as FieldcollectionData;
use Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData as BrickData;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function getByLiteralFieldName(Product $p): void
{
    assertType('string|null', $p->get('sku'));
    assertType('Carbon\Carbon|null', $p->get('releaseDate'));
}

function getMatchesTheGetterCaseInsensitively(Product $p): void
{
    assertType('string|null', $p->get('SKU'));
    assertType('Carbon\Carbon|null', $p->get('ReleaseDATE'));
}

function getLocalizedFieldWithLanguage(Product $p): void
{
    assertType('string|null', $p->get('title', 'en'));
    assertType('string|null', $p->get('title'));
}

function getOnBaseClassesStaysMixed(Concrete $c, AbstractObject $o): void
{
    assertType('mixed', $c->get('sku'));
    assertType('mixed', $o->get('sku'));
    assertType('mixed', $c->get('id'));
}

/**
 * @param 'sku'|'price' $constantUnion
 */
function getWithNonLiteralNames(Product $p, string $name, string $constantUnion): void
{
    assertType('mixed', $p->get($name));
    assertType('float|string|null', $p->get($constantUnion));
    assertType('string|null', $p->get(Product::FIELD_SKU));
}

function getUnknownFieldStaysMixed(Product $p): void
{
    assertType('mixed', $p->get('nosuch'));
}

function getOnBrickAndFieldcollectionItems(Dimensions $brick, PriceTier $item): void
{
    assertType('float|null', $brick->get('width'));
    assertType('float|null', $item->get('minQuantity'));
}

function getOnBaseItemClassesStaysMixed(BrickData $brick, FieldcollectionData $item): void
{
    assertType('mixed', $brick->get('width'));
    assertType('mixed', $item->get('minQuantity'));
}

/**
 * @param 'sku'|'price' $constantUnion
 */
function getValueForFieldName(Product $p, string $name, string $constantUnion): void
{
    assertType('string|null', $p->getValueForFieldName('sku'));
    assertType('Carbon\Carbon|null', $p->getValueForFieldName('releaseDate'));
    assertType('int|null', $p->getValueForFieldName('id'));
    assertType('float|string|null', $p->getValueForFieldName($constantUnion));
    assertType('mixed', $p->getValueForFieldName($name));
}

function getValueForFieldNameReadsThePropertyCaseSensitively(Product $p): void
{
    assertType('mixed', $p->getValueForFieldName('SKU'));
    assertType('mixed', $p->getValueForFieldName('nosuch'));
}

function getValueForFieldNameOfLocalizedFieldIsNull(Product $p): void
{
    // Localized fields have no property, so Pimcore always returns null for them.
    assertType('null', $p->getValueForFieldName('title'));
    assertType('Pimcore\Model\DataObject\Localizedfield<Pimcore\Model\DataObject\Product>|null', $p->getValueForFieldName('localizedfields'));
}

function getValueForFieldNameOfCalculatedValueIsTheGetters(Product $p): void
{
    // Pimcore computes it, as its getter does; Pimcore types that getter `mixed`.
    assertType('mixed', $p->getValueForFieldName('margin'));
}

function getValueForFieldNameOfCalculatedValueOnClassOverrideIsTheGetters(RedeclaringOverriddenProduct $p): void
{
    // The override types the getter of the generated class's calculated value.
    assertType('float|null', $p->getValueForFieldName('margin'));
    assertType('mixed', $p->getValueForFieldName('Margin'));
}

function getValueForFieldNameOnConcreteStaysMixed(Concrete $c): void
{
    assertType('mixed', $c->getValueForFieldName('sku'));
    assertType('mixed', $c->getValueForFieldName('id'));
}

function genericGettersWithNamedArguments(Product $p): void
{
    assertType('string|null', $p->get(language: 'en', fieldName: 'title'));
    assertType('string|null', $p->getValueForFieldName(key: 'sku'));
}

function localizedfieldsKnowTheirOwner(Product $p): void
{
    assertType('Pimcore\Model\DataObject\Localizedfield<Pimcore\Model\DataObject\Product>|null', $p->getLocalizedfields());
}

function getLocalizedValue(Product $p): void
{
    $lf = $p->getLocalizedfields();
    if ($lf === null) {
        return;
    }

    assertType('string|null', $lf->getLocalizedValue('title', 'en'));
    assertType('string|null', $lf->getLocalizedValue('description'));
    assertType('mixed', $lf->getLocalizedValue('sku'));
    assertType('mixed', $lf->getLocalizedValue('nosuch'));
}

function getLocalizedValueWithUnknownOwnerStaysMixed(Localizedfield $lf): void
{
    assertType('Pimcore\Model\DataObject\Localizedfield', $lf);
    assertType('mixed', $lf->getLocalizedValue('title', 'en'));
}

/**
 * @param Localizedfield<Product> $lf
 */
function pimcoreLocalizedfieldMethodTagsAreKept(Localizedfield $lf, Product $p): void
{
    assertType('Pimcore\Model\DataObject\Localizedfield\Dao', $lf->getDao());
    assertType('null', $lf->save());
    assertType('bool', $lf->delete());
    assertType('null', $lf->load($p));
    assertType('null', $lf->createUpdateTable());
}
