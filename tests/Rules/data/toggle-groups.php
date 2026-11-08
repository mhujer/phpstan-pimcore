<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleGroups;

use Pimcore\Cache;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;
use Pimcore\Model\Version;

function everyBuiltInToggle(Localizedfield $localizedfield): void
{
    DataObject::setHideUnpublished(true);
    Document::setHideUnpublished(true);
    DataObject::setGetInheritedValues(true);
    Document\Page::setGetInheritedValues(true);
    DataObject::setGetInheritedProperties(true);
    $localizedfield::setGetFallbackValues(true);
    Localizedfield::setStrictMode(true);
    DataObject::disableDirtyDetection();
    Version::disable();
    Cache::disable();
    RuntimeCache::disable();
}

function matchedByDeclaringClass(Product $product): void
{
    Product::setHideUnpublished(true);
    $product->setGetInheritedValues(true);
    $product::disableDirtyDetection();
}

/**
 * A call restores only its own group, but any method of the group does.
 */
function groupsAreSeparate(): void
{
    Version::disable();
    try {
        Cache::disable();
    } finally {
        Cache::enable();
        RuntimeCache::enable();
    }

    DataObject::setDisableDirtyDetection(true);
    try {
    } finally {
        DataObject::enableDirtyDetection();
    }

    DataObject::setGetInheritedProperties(true);
    try {
    } finally {
        Document::setGetInheritedProperties(false);
    }
}

/**
 * A dynamic method name can't be resolved, but a class-string receiver can.
 *
 * @param class-string<DataObject> $class
 */
function dynamicCalls(string $class, string $method): void
{
    $class::$method(true);
    DataObject::$method(true);
    call_user_func([DataObject::class, 'setHideUnpublished'], true);
    $class::setHideUnpublished(true);
}
