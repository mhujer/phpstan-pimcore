<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomElementService;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use function PHPStan\Testing\assertType;

assertType('Pimcore\\Model\\Asset|null', Service::getElementById('asset', 1));
assertType('Pimcore\\Model\\Document|null', Service::getElementById('document', 1));
assertType('Pimcore\\Model\\DataObject|null', Service::getElementById('object', 1));
assertType('null', Service::getElementById('dataobject', 1));
assertType('Pimcore\\Model\\Asset|null', Service::getElementByPath('asset', '/'));
assertType('Pimcore\\Model\\Document|null', Service::getElementByPath('document', '/'));
assertType('Pimcore\\Model\\DataObject|null', Service::getElementByPath('object', '/'));
assertType('null', Service::getElementByPath('Object', '/'));
assertType('Pimcore\\Model\\Asset|null', Service::getElementById(id: 1, type: 'asset'));

/**
 * @param 'asset'|'document' $union
 * @param 'asset'|'objects' $partlyInvalid
 */
function lookupTypes(string $type, string $union, string $partlyInvalid): void
{
    assertType('Pimcore\\Model\\Asset|Pimcore\\Model\\Document|null', Service::getElementById($union, 1));
    assertType('Pimcore\\Model\\Asset|Pimcore\\Model\\Document|null', Service::getElementByPath($union, '/'));
    assertType('Pimcore\\Model\\Asset|null', Service::getElementById($partlyInvalid, 1));
    assertType('Pimcore\\Model\\Asset|null', Service::getElementByPath($partlyInvalid, '/'));
    assertType('Pimcore\\Model\\Asset|Pimcore\\Model\\DataObject\\AbstractObject|Pimcore\\Model\\Document|null', Service::getElementById($type, 1));
    assertType('Pimcore\\Model\\Element\\ElementInterface|null', Service::getElementByPath($type, '/'));
}

function elementTypeLiterals(ElementInterface $element, string $className): void
{
    assertType("'asset'|'document'|'object'|null", Service::getElementType($element));
    assertType("'asset'|'document'|'object'|null", Service::getElementTypeByClassName($className));
    assertType('string|null', CustomElementService::getElementType($element));
    assertType('string|null', CustomElementService::getElementTypeByClassName($className));
}
