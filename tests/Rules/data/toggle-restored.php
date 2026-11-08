<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleRestored;

use Pimcore\Model\DataObject;

function restoredInFinally(): void
{
    $previous = DataObject::doHideUnpublished();
    DataObject::setHideUnpublished(true);
    try {
        doWork();
    } finally {
        DataObject::setHideUnpublished($previous);
    }
}

function restoredToALiteralInsideAnIf(bool $was): void
{
    DataObject::setHideUnpublished(true);
    try {
        doWork();
    } finally {
        if ($was) {
            DataObject::setHideUnpublished(false);
        }
    }
}

function restoredOutsideFinally(): void
{
    DataObject::setHideUnpublished(true);
    doWork();
    DataObject::setHideUnpublished(false);
}

function doWork(): void
{
}
