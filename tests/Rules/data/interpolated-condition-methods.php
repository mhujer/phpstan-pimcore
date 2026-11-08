<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\InterpolatedConditionMethods;

use ArrayObject;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;

function methods(Product\Listing $list, string $name, string $condition, bool $quote): void
{
    $list->addConditionParam("name = '" . $name . "'");
    $list->setOrderKey('o_' . $name, false);
    $list->setGroupBy('o_' . $name, false);
    $list->setOrderKey('o_' . $name, $quote);
    $list->setGroupBy(qoute: false, groupBy: 'o_' . $name);
    $list->setCondition(conditionVariables: [], condition: 'name = ' . $name);
    $list?->setCondition('name = ' . $name);
    $list->SETCONDITION('name = ' . $name);

    $list->setOrderKey('o_' . $name);
    $list->setOrderKey('o_' . $name, true);
    $list->setGroupBy('o_' . $name, qoute: true);
    $list->setOrderKey(['o_' . $name], false);
    $list->setCondition($condition);
    $list->setCondition('name = ?', [$name]);
    $list->addConditionParam('name = ?', $name);
    $list->addFilterByField('name', '=', "'" . $name . "'");
    $list->setConditionVariableTypes(['name' => 'x' . $name]);
}

function receivers(
    DataObject\Listing $objects,
    Asset\Listing $assets,
    ArrayObject $other,
    ?Product\Listing $maybe,
    string $name,
): void {
    $objects->setCondition('name = ' . $name);
    $assets->setCondition('name = ' . $name);
    $other->setCondition('name = ' . $name);
    $maybe?->setCondition('name = ' . $name);
}
