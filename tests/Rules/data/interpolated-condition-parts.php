<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\InterpolatedConditionParts;

use Doctrine\DBAL\Connection;
use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\Status;
use Pimcore\Db;
use Pimcore\Model\DataObject\Product;

function unsafeParts(Product\Listing $list, string $name, mixed $value, int|string $id, string $field): void
{
    $list->setCondition("name = '" . $name . "'");
    $list->setCondition("name = '$name'");
    $list->setCondition("id = $id AND name = '{$name}'");
    $list->setCondition('id = ' . $value);
    $list->setCondition('name LIKE ' . $list->escapeLike($name));
    $list->setCondition('name = ' . $list->quote($name) . ' AND ' . $field . ' = 1');
}

function safeParts(
    Product\Listing $list,
    Connection $db,
    string $name,
    int $id,
    float $price,
    bool $flag,
    ?int $maybe,
    Status $status,
    string $field,
): void {
    $list->setCondition('id = ' . $id . ' AND price > ' . $price . ' AND flag = ' . $flag . ' AND x = ' . $maybe);
    $list->setCondition("id = $id AND price > {$price}");
    $list->setCondition("status = '" . Status::Active->value . "' OR status = '" . $status->value . "'");
    $list->setCondition('name = ' . $list->quote($name) . ' AND ' . $list->quoteIdentifier($field) . ' = 1');
    $list->setCondition('name = ' . $db->quote($name) . ' AND ' . $db->quoteIdentifier($field) . ' = 1');
    $list->setCondition('name = ' . Db::get()->quote($name) . ' AND ' . $db->quoteSingleIdentifier($field) . ' = 1');
    $list->setCondition('name LIKE ' . $list->quote('%' . $list->escapeLike($name) . '%'));
    $list->setCondition('a = 1' . ' AND b = ' . PHP_INT_MAX);
}
