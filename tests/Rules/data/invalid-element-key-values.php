<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

/** @param array<string, mixed> $values */
function elementKeyValues(Asset $asset, Document $document, Product $product, string $dynamic, array $values): void
{
    Product::create(['key' => 'bad/name']);
    Asset::create(data: ['key' => 'bad?name'], parentId: 1);
    Document::create(1, ['key' => 'bad&name']);
    Folder::create(['key' => 'bad<name']);
    $product->setValues(['key' => 'prefix/' . $dynamic]);
    $asset->setValues(['key' => "name?{$dynamic}"]);
    $document->setValue(value: 'bad@name', key: 'key');
    $product->setValues(['KEY' => 'bad/name']);
    $asset->setValues(['key' => 'valid&name']);
    $document->setValues(['key' => $dynamic]);
    $product->setValues($values);
    $asset->setValues([...$values]);
    $asset->setValues(['filename' => 'bad/name']);
    $product->setValues(['other' => 'bad/name']);
}
