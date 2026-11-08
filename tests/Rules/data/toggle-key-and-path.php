<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleKeyAndPath;

use Pimcore\Model\DataObject\Product;

function neverRestored(): void
{
    Product::setDoNotRestoreKeyAndPath(true);
}

function restoredOutsideFinally(): void
{
    Product::setDoNotRestoreKeyAndPath(true);
    Product::setDoNotRestoreKeyAndPath(false);
}

function restoredInFinally(): void
{
    Product::setDoNotRestoreKeyAndPath(true);
    try {
    } finally {
        Product::setDoNotRestoreKeyAndPath(false);
    }
}
