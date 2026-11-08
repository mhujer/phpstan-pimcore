<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleImmediateCacheWrites;

use Pimcore\Cache;

function neverRestored(): void
{
    Cache::setForceImmediateWrite(true);
}

function restoredOutsideFinally(): void
{
    Cache::setForceImmediateWrite(true);
    Cache::setForceImmediateWrite(false);
}

function restoredInFinally(): void
{
    Cache::setForceImmediateWrite(true);
    try {
    } finally {
        Cache::setForceImmediateWrite(false);
    }
}

function enablingCacheDoesNotRestoreImmediateWrites(): void
{
    Cache::setForceImmediateWrite(true);
    Cache::disable();
    try {
    } finally {
        Cache::enable();
    }
}

function restoringImmediateWritesDoesNotEnableCache(): void
{
    Cache::disable();
    Cache::setForceImmediateWrite(true);
    try {
    } finally {
        Cache::setForceImmediateWrite(false);
    }
}
