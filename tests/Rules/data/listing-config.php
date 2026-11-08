<?php

declare(strict_types=1);

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

function listingConfig(Product\Listing $list, bool $choose): void
{
    Asset::getList(['ORDER' => 'bad']);
    Document::getList(['orderKey' => 'a, b']);
    DataObject::getList(['groupBy' => '`a, b`']);
    $list->setValues(['OBJECTTYPES' => ['Object']]);
    $list->setOrder($choose ? 'bad1' : 'bad2');
    $list->setOrderKey(['good', 'bad key', 'bad(key)']);
    $list->setGroupBy('`a, DATE(b)`');
}
