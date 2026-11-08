<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleRemoveDynamicOptions;

use Pimcore\Model\DataObject\ClassDefinition\Service;

function neverRestored(): void
{
    Service::setDoRemoveDynamicOptions(true);
}

function restoredOutsideFinally(): void
{
    Service::setDoRemoveDynamicOptions(true);
    Service::setDoRemoveDynamicOptions(false);
}

function restoredInFinally(): void
{
    Service::setDoRemoveDynamicOptions(true);
    try {
    } finally {
        Service::setDoRemoveDynamicOptions(false);
    }
}
