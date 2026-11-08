<?php

declare(strict_types=1);

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

function listingVariables(Product\Listing $list): void
{
    $list->setConditionVariables([]);
    Product::getList(['conditionVariables' => []]);
    $list->setValues(['conditionVariables' => []]);
    Asset::getList(['conditionVariables' => []]);
    Document::getList(['conditionVariables' => []]);
    DataObject::getList(['conditionVariables' => []]);
}
