<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

/**
 * @param array<string, int> $namedValues
 * @param list<int> $ids
 * @param array{0: int, 1?: int} $optional
 * @param 'a = ?'|'a = ? AND b = ?' $oneMatches
 * @param 'a = ? AND b = ?'|'a = ? AND b = ? AND c = ?' $allWrong
 */
function dynamicConditions(Listing $list, array $ids, array $optional, string $sql, string $oneMatches, string $allWrong, int $id, float $number, string $field, int|float $numeric, array $namedValues): void
{
    $list->setCondition('a IN (?)', $ids);
    $list->addConditionParam('a IN (?)', $ids);
    $list->setCondition('a = ? AND b = ?', $ids);
    $list->setCondition('a = ?', $optional);
    $list->setCondition($sql, []);
    $list->setCondition($oneMatches, [1]);
    $list->setCondition($allWrong, [1]);
    $list->setCondition('id > ' . $id . ' AND a = ? AND b = ?', [1]);
    $list->addConditionParam("id > $number AND a = ? AND b = ?", 1);
    $list->setCondition($field . ' = ?', []);
    $list->setCondition("'$field' = ?", []);
    $list->setCondition('a = ?', [1]);
    $list->addConditionParam('a = ?', null);
    $list->setCondition('a = ? AND b = ?', [1, 2, 3]);
    $list->addConditionParam('a = ? AND b = ?', [1, 2, 3]);
    $list->setCondition(condition: 'a = ? AND b = ?', conditionVariables: [1]);
    $list->setCondition('a = :name' . $id, ['name1' => 1]);
    $list->setCondition('-' . $id . ' ? ', []);
    $list->setCondition('a = ?', [[1, 2], [3, 4]]);
    $list->addConditionParam('a = ?', [[1, 2], [3, 4]]);
    $list->setCondition('a = ?', [1]);
    $list->addConditionParam('b = ? AND c = ?', 2);
    $list->setCondition('id > ' . $numeric . ' AND a = ? AND b = ?', [1]);
    $list->setCondition('a = ?', $namedValues);
}
