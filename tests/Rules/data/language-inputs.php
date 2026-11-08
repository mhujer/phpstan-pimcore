<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Product;

/** @param Localizedfield<Product> $localized */
function languageInputs(Product $product, Localizedfield $localized, bool $condition): void
{
    $language = $condition ? 'en' : 'fr';
    $product->getTitle(language: 'fr');
    $product->setTitle(language: 'fr', title: 'value');
    $product->get(language: 'fr', fieldName: 'title');
    $product->set(language: 'fr', value: 'value', fieldName: 'title');
    $localized->getLocalizedValue(language: 'fr', name: 'title');
    Product::getByTitle(locale: 'fr', value: 'title');
    $product->getTitle('default');
    $product->getTitle($language);
    $product->getTitle(...['fr']);
    $product->getLocalizedfields();
    $product->get('TITLE', 'fr');
    $product->set('Title', 'value', 'fr');
}
