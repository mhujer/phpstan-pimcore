<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleMailDebugMode;

use Pimcore\Mail;

function neverRestored(): void
{
    Mail::setForceDebugMode(true);
}

function restoredOutsideFinally(): void
{
    Mail::setForceDebugMode(true);
    Mail::setForceDebugMode(false);
}

function restoredInFinally(): void
{
    Mail::setForceDebugMode(true);
    try {
    } finally {
        Mail::setForceDebugMode(false);
    }
}
