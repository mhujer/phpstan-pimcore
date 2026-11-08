<?php

declare(strict_types=1);

namespace UnknownDefinitionNames;

use Pimcore\Model\DataObject\ClassDefinition;

function classDefinitions(): void
{
    ClassDefinition::getByName('MissingProduct');
    ClassDefinition::getByName('Product');
}
