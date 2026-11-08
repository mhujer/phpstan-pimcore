<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

function namedConditions(Listing $list): void
{
    $list->setCondition('a = :name', ['other' => 1]);
    $list->addConditionParam('a = :name', ['name' => 1, 'foo' => 2]);
    $list->setCondition('a = :name', [':name' => 1]);
    $list->setCondition('a = :name OR b = :name', ['name' => 1]);
    $list->setCondition('a = ? AND b = :name', [1, 'name' => 2]);
    $list->addConditionParam('a = ? AND b = :name', [1, 'name' => 2]);
    $list->setCondition('a = :name', 1);
    $list->setCondition('a = ?', ['name' => 1]);
    $list->addConditionParam('a = :name', ['other' => 1]);
    $list->setCondition('a = :name', ['name' => 1, 2]);
}
