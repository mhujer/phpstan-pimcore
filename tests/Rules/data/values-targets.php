<?php

declare(strict_types=1);

namespace ValuesTargets;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

Asset::create(1, ['filename' => 'a', 'data' => '', 'children' => [], 'customSetting' => []]);
Asset::create(1, ['completelyUnknown' => 'x']);
Document::create(1, ['title' => 'x']);
Document\Page::create(1, ['title' => 'x']);
DataObject\Folder::create(['key' => 'x']);
DataObject\Folder::create(['completelyUnknown' => 'x']);
DataObject::getList(['limitt' => 10]);
Asset::getList(['limitt' => 10]);
Document::getList(['limitt' => 10]);
Product::getList(['limitt' => 10, 'class' => Product::class]);
Product::getList(['class' => DataObject::class, 'filterBySku' => 'x']);
Product::getList(['filterBySku' => 'x']);

function dynamicClass(string $class): void
{
    Product::getList(['class' => $class, 'completelyUnknown' => 'x']);
}
Product::getList(['class' => '\\Pimcore\\Model\\Asset', 'objectTypes' => ['object']]);
Product::getList(['class' => 'Product', 'completelyUnknown' => 'x']);
Product::getList(['class' => '', 'limit' => 10]);
