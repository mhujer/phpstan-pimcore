<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleInheritanceRuntimeCache;

use Pimcore\Model\DataObject\Concrete\Dao\InheritanceHelper;

function neverRestored(): void
{
    InheritanceHelper::setUseRuntimeCache(true);
}

function restoredOutsideFinally(): void
{
    InheritanceHelper::setUseRuntimeCache(true);
    InheritanceHelper::setUseRuntimeCache(false);
}

function restoredInFinally(): void
{
    InheritanceHelper::setUseRuntimeCache(true);
    try {
    } finally {
        InheritanceHelper::setUseRuntimeCache(false);
    }
}
