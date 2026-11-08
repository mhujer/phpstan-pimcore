<?php

declare(strict_types=1);

namespace UnknownDefinitionKeys;

use Pimcore\Model\DataObject\Fieldcollection\Definition as CollectionDefinition;
use Pimcore\Model\DataObject\Objectbrick\Definition as BrickDefinition;

function definitionKeys(): void
{
    CollectionDefinition::getByKey('MissingCollection');
    BrickDefinition::getByKey(key: 'MissingBrick');
    CollectionDefinition::getByKey('PriceTier');
    BrickDefinition::getByKey('Electrical');
}
