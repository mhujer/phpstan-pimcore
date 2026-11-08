<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\Product\Listing;

/** @param Localizedfield<Product> $localized */
function languageArguments(Product $product, Localizedfield $localized, Listing $listing, ?Product $nullable): void
{
    $product->setTitle('title', 'fr');
    $product->get('title', 'fr');
    $product->set('title', 'title', 'fr');
    $localized->getLocalizedValue('title', 'fr');
    $localized->setLocalizedValue('title', 'title', 'fr');
    Product::getByTitle('title', 'fr');
    Product::getByLocalizedfields('title', 'title', 'fr');
    $listing->setLocale('fr');
    $nullable?->getTitle('fr');
    $product->get('sku', 'fr');
    $product->set('sku', 'sku', 'fr');
    Product::getBySku('sku', 1);
    $product->getTitle(...);
    $product->getTitle(...['en']);
}
