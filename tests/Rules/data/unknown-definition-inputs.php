<?php

declare(strict_types=1);

namespace UnknownDefinitionInputs;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Fieldcollection\Definition as CollectionDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition as BrickDefinition;

/** @param 'Product'|'MissingCategory' $choice */
function constantNames(string $choice): void
{
    ClassDefinition::GETBYNAME(name: 'MissingNamed');
    ClassDefinition::getByName($choice);
}

/** @param list<string> $args */
function dynamicNames(string $name, array $args): void
{
    ClassDefinition::getByName($name);
    CollectionDefinition::getByKey($name);
    BrickDefinition::getByKey($name);
    ClassDefinition::getByName(...$args);
    CollectionDefinition::getByKey(...$args);
    BrickDefinition::getByKey(...$args);
    ClassDefinition::getByName(...);
    CollectionDefinition::getByKey(...);
    BrickDefinition::getByKey(...);
    ClassDefinition::getByName();
    ClassDefinition::getById('MissingId');
    CustomDefinition::getByName('AnyCustomName');
    Unrelated::getByName('AnyUnrelatedName');
}

final class CustomDefinition extends ClassDefinition
{
    public static function getByName(string $name): ?ClassDefinition
    {
        return $name === '' ? null : new self();
    }
}

final class Unrelated
{
    public static function getByName(string $name): ?self
    {
        return $name === '' ? null : new self();
    }
}
