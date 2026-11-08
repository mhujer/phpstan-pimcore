<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomCopyService;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomElementService;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverriddenProduct;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element;
use function PHPStan\Testing\assertType;

function cloneProduct(DataObject\Product $product): void
{
    assertType('Pimcore\\Model\\DataObject\\Product', Element\Service::cloneMe($product));
}

function copyProduct(DataObject\Service $service, DataObject\Folder $target, DataObject\Product $product): void
{
    assertType('Pimcore\\Model\\DataObject\\Product', $service->copyAsChild($target, $product));
    assertType('Pimcore\\Model\\DataObject\\Product', $service->COPYASCHILD(source: $product, target: $target));
}

/**
 * @param list<Element\ElementInterface> $elements
 * @param list<DataObject\AbstractObject> $objects
 */
function unpackCopies(DataObject\Service $service, array $elements, array $objects): void
{
    assertType('Pimcore\\Model\\Element\\ElementInterface', Element\Service::cloneMe(...$elements));
    assertType('Pimcore\\Model\\DataObject\\AbstractObject', $service->copyAsChild(...$objects));
}

function customCopyMethods(CustomCopyService $service, DataObject\Folder $target, DataObject\Product $product): void
{
    assertType('Pimcore\\Model\\Element\\ElementInterface', CustomElementService::cloneMe($product));
    assertType('Pimcore\\Model\\DataObject\\AbstractObject', $service->copyAsChild($target, $product));
}

function cloneElements(Asset\Image $image, Document\Page $page, DataObject\Product|DataObject\Category $object, Element\ElementInterface $element): void
{
    assertType('Pimcore\\Model\\Asset\\Image', Element\Service::CLONEME(element: $image));
    assertType('Pimcore\\Model\\Document\\Page', Document\Service::cloneMe($page));
    assertType('Pimcore\\Model\\DataObject\\Category|Pimcore\\Model\\DataObject\\Product', Element\Service::cloneMe($object));
    assertType('Pimcore\\Model\\Element\\ElementInterface', Element\Service::cloneMe($element));
}

function copyClassOverride(DataObject\Service $service, DataObject\Folder $target, OverriddenProduct $product): void
{
    assertType('Mhujer\\PHPStan\\Pimcore\\Tests\\Type\\Fixtures\\OverriddenProduct', Element\Service::cloneMe($product));
    assertType('Mhujer\\PHPStan\\Pimcore\\Tests\\Type\\Fixtures\\OverriddenProduct', $service->copyAsChild($target, $product));
}
