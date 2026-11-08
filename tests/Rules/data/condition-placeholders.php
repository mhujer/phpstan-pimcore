<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

function conditions(Listing $list): void
{
    $list->setCondition('a = ? AND b = ?', [1]);
    $list->setCondition('a = ?', [1, 2]);
    $list->addConditionParam('a = ? AND b = ?', 1);
    $list->addConditionParam('a = ?', [1, 2]);
    $list->setCondition('a = ?', []);
    $list->setCondition('a = ?', null);
    $list->setCondition('a = 1', [1]);
    $list->addConditionParam('a = 1', [1]);
    $list->setCondition('a = ?');
    $list->addConditionParam('a = ?');
    $list->setCondition('a IN (?)', [[1, 2]]);
}
