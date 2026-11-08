<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\InterpolatedConditionFunctions;

use Pimcore\Model\DataObject\Product;
use function sprintf;

/**
 * @param list<int> $ids
 * @param list<string> $names
 * @param list<int> $more
 */
function sprintfAndImplode(Product\Listing $list, string $name, string $id, string $format, array $ids, array $names, array $more): void
{
    $list->setCondition(sprintf('id = %d AND price > %.2f AND x = %u AND hex = %X', $id, $id, $id, $id));
    $list->setCondition(sprintf("id = %2\$d AND name = '%1\$s'", 'fixed', $id));
    $list->setCondition(sprintf('name = %s', $list->quote($name)));
    $list->setCondition(sprintf('100%% AND id IN (%s)', implode(',', $ids)));
    $list->setCondition(sprintf('id = %d', ...$more));
    $list->setCondition(sprintf('id = %d', $id) . ' AND 1');
    $list->setCondition('id IN (' . implode(',', array_fill(0, count($names), '?')) . ')');
    $list->setCondition(implode(' AND ', ['a = 1', 'b = ' . 2]));
    $list->setCondition(join(',', $ids));
    $list->setCondition(implode($ids));
    $list->setCondition(implode(separator: ',', array: $ids));

    $list->setCondition(sprintf("name = '%s'", $name));
    $list->setCondition(sprintf("id = %d AND name = '%2\$s'", $id, $name));
    $list->setCondition(sprintf($format, $id));
    $list->setCondition(sprintf('name IN (%s)', implode(',', $names)));
    $list->setCondition(implode(' OR ', $names));
    $list->setCondition(implode($name, $ids));
    $list->setCondition(sprintf('x = %s', ...$names));
}
