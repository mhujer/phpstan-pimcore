<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\CustomLookupAsset;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Element\Service;

Product::getById(1, ['froce' => true]);
Asset::getByPath('/', ['force' => 1]);
Service::getElementById('object', 1, ['force' => 'yes']);
Page::getById(1, ['cache' => false]);
DataObject::getByPath('/', ['Force' => true]);
Document::getByPath('/', ['force' => null]);
Asset::getById(1, ['force' => []]);
Service::getElementById(params: ['froce' => true], id: 1, type: 'asset');
Asset::getById(params: ['force' => 'yes'], id: 1);
DataObject\Folder::getById(1, ['cache' => false]);
Asset\Image::getById(1, ['cache' => false]);

/**
 * @param array<string, mixed> $options
 * @param class-string<Asset> $class
 */
function safeLookupOptions(bool $bool, mixed $mixed, array $options, string $key, string $class): void
{
    Asset::getById(1, ['force' => $bool]);
    Document::getByPath('/', ['force' => $mixed]);
    Asset::getById(1, $options);
    Asset::getById(1, [...$options]);
    Asset::getById(1, [$key => true]);
    $class::getById(1, ['x' => 1]);
    CustomLookupAsset::getById(1, ['x' => 1]);
    DataObject::getById(1, ['force' => 1]);
    DataObject::getByPath('/', ['force' => 1]);
    Product::getById(1, ['force' => 1]);
    DataObject\Folder::getById(1, ['force' => 1]);
    Service::getElementByPath('asset', '/');
    Service::getElementById('asset', 1, $options);
    Asset::getById(...[1, ['x' => 1]]);
    Asset::getById(1, ['force' => $bool ? 1 : false]);
    $lookup = Asset::getById(...);
    $lookup(1, ['x' => 1]);
    $elementLookup = Service::getElementById(...);
    $elementLookup('asset', 1);
    Asset::getByPath('/');
    Document::getById(1);
    Product::getByPath('/');
}
