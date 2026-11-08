<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\InterpolatedConditionConfig;

use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\ConfigListing;
use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\ConfigProduct;
use Pimcore\Db;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;

/** @param array<string, mixed> $config */
function config(Product\Listing $list, string $name, int $id, array $config, string $condition, string $key): void
{
    Product::getList(['condition' => 'name = ' . $name]);
    Product::getList(['Condition' => "name = '$name'"]);
    DataObject::getList(['condition' => sprintf('name = %s', $name)]);
    Asset::getList(['condition' => 'name = ' . $name]);
    Document::getList(['condition' => 'name = ' . $name]);
    $list->setValues(['condition' => 'name = ' . $name]);
    $list->setValue('condition', 'name = ' . $name);
    $list->setValue(value: 'name = ' . $name, key: 'Condition');
    Product::getList(config: ['CONDITION' => 'name = ' . $name . $key]);
    $list?->setValues(['condition' => 'name = ' . $name]);
    $list->setValues(['condition' => implode(',', [$name])]);

    Product::getList(['condition' => 'id = ' . $id]);
    Product::getList(['condition' => 'name = ' . Db::get()->quote($name)]);
    Product::getList(['condition' => 'name = ' . Db::get()->quoteIdentifier($name)]);
    Product::getList(['condition' => sprintf('id = %d', $name)]);
    Product::getList($config);
    Product::getList(['condition' => $condition]);
    Product::getList([...['condition' => 'name = ' . $name]]);
    Product::getList([$key => 'name = ' . $name]);
    Product::getList(['orderKey' => 'o_' . $name]);
    Product::getList(['condition' => 'sku = ?']);
    $list->setValues(['condition' => 'name = ' . $list->quote($name)]);
    $list->setValue('orderKey', 'o_' . $name);
    $list->setValue($key, 'name = ' . $name);
    $list->setValue('condition', $condition);
    $list->setValues($config);
    $list->setValues([...['condition' => 'name = ' . $name]]);
    $list->setValues(['condition' => 'id = ' . $id]);
}

function custom(ConfigListing $list, string $name): void
{
    $list->setValues(['condition' => 'name = ' . $name]);
    $list->setValue('condition', 'name = ' . $name);
    ConfigProduct::getList(['condition' => 'name = ' . $name]);
    Product::create(['condition' => 'name = ' . $name]);
    (new Product())->setValues(['condition' => 'name = ' . $name]);
}
