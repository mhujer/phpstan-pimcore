<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

function filters(Listing $list, string $operator): void
{
    $list->filterBySku('a', 'BETWEEN ? AND ?');
    $list->filterById(1, 'BETWEEN ? AND ?');
    $list->filterByKey(['a', 'b'], 'IN (?)');
    $list->filterBySku([], '=');
    $list->filterBySku('a');
    $list->filterBySku('a', 'LIKE');
    $list->filterById([1, 2], 'BETWEEN ? AND ?');
    $list->filterByKey([['a', 'b']], 'IN (?)');
    $list->filterByKey(['a'], 'IN (?)');
    $list->filterById([1, 2], $operator);
    $list->filterBySku(...);
}
