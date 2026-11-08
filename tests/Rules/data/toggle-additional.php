<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleAdditional;

use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\FeatureSwitches;
use Pimcore\Model\DataObject;

function additionalToggleNotRestored(): void
{
    FeatureSwitches::disableIndexing();
}

function additionalToggleRestoredInFinally(): void
{
    FeatureSwitches::disableIndexing();
    try {
        // The built-in toggles still apply.
        DataObject::setHideUnpublished(true);
    } finally {
        FeatureSwitches::enableIndexing();
    }
}
