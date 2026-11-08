<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleScopes;

use Pimcore\Model\DataObject;

DataObject::setHideUnpublished(true);

function restoreInTheEnclosingFunctionDoesNotCount(): void
{
    $closure = static function (): void {
        DataObject::setHideUnpublished(true);
    };
    $arrow = static fn () => DataObject::setGetInheritedValues(true);
    try {
        $closure();
        $arrow();
    } finally {
        DataObject::setHideUnpublished(false);
        DataObject::setGetInheritedValues(false);
    }
}

function finallyOfTheEnclosingFunctionDoesNotCount(): void
{
    try {
    } finally {
        $closure = static function (): void {
            DataObject::setHideUnpublished(true);
            DataObject::setHideUnpublished(false);
        };
        $closure();
    }
}

final class InAClass
{
    public function method(): object
    {
        DataObject::setHideUnpublished(true);

        return new class() {
            public function method(): void
            {
                try {
                } finally {
                    DataObject::setHideUnpublished(false);
                }
            }
        };
    }
}
