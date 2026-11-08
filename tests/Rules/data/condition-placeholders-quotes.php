<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

function quotedConditions(Listing $list): void
{
    $list->setCondition("a = '?'", []);
    $list->addConditionParam("a = '?'", [1, 2]);
    $list->setCondition('`?` = 1 AND [?:name] = 2', []);
    $list->setCondition('/* ? :name */ a = 1 -- ?', []);
    $list->setCondition('a::text = 1 AND b = ??', []);
    $list->setCondition('a = "?:name" AND b = ?', [1]);
    $list->addConditionParam('/* ? */ a = 1', 1);
    $list->addConditionParam('a::text = 1', 1);
    $list->addConditionParam('a = ??', 1);
    $list->addConditionParam('`?` = 1', 1);
}
