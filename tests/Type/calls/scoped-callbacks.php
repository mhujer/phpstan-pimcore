<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Calls;

use Pimcore\Model\DataObject\Service;

function callbackArguments(): string
{
    return Service::useInheritedValues(true, static fn (string $value): string => $value, ['argument']);
}

function uncheckedCallbackArguments(): string
{
    return Service::useInheritedValues(fnArgs: [42], fn: static fn (string $value): string => $value, inheritValues: true);
}

function voidCallback(): null
{
    return Service::useInheritedValues(true, static function (): void {
    });
}
