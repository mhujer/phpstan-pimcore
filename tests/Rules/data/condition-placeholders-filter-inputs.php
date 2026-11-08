<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

/** @param list<string> $values
 * @param array{0?: string, 1?: string} $optional
 * @param list<mixed> $arguments
 * @param '='|'BETWEEN ? AND ?' $validOperator
 * @param '<>'|'IN (?)' $invalidRelationOperator
 */
function filterInputs(Listing $list, array $values, array $optional, array $arguments, string $validOperator, string $invalidRelationOperator): void
{
    $list->filterBySku(operator: 'BETWEEN ? AND ?', data: 'a');
    $list->filterByKey($values, 'IN (?)');
    $list->filterBySku('a', "= '?' /* ? */");
    $list->filterBySku($optional, 'BETWEEN ? AND ?');
    $list->filterBySku('a', $validOperator);
    $list->filterBySku(...$arguments);
    $list->filterBySku('a', 'LIKE /* ? */');
    $list->filterBySku('a', '= :sku');
    $list->filterByMainCategory(['id' => 1, 'type' => 'object'], $validOperator);
    $list->filterByMainCategory(['id' => 1, 'type' => 'object'], $invalidRelationOperator);
}

(new InheritedFilters())->filterBySku('a', 'BETWEEN ? AND ?');
(new CustomFilters())->filterBySku(['a', 'b'], 'IN (?)');
(new CustomFilters())->filterByCustom(['a', 'b'], 'IN (?)');
(new Listing())->filterByMissing(['a', 'b'], 'IN (?)');
