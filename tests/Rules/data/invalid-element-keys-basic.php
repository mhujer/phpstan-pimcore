<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

function elementKeys(Asset $asset, Document $document, Product $product, string $dynamic): void
{
    $asset->setKey('folder/file.jpg');
    $document->setKey('page?query');
    $product->setKey('product<name');
    $asset->setKey('image#name');
    $document->setKey('page&name');
    $asset->setKey('valid&name.jpg');
    $product->setKey('valid?#&@=;+name');
    $document->setKey('valid-name');
    $asset->setKey($dynamic);
}
