<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\ScopedCallbackProvider;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\ScopedService;
use Pimcore\Model\DataObject\Service;
use RuntimeException;
use function PHPStan\Testing\assertType;

assertType('42', Service::useInheritedValues(true, static fn (): int => 42));

/**
 * @param (callable(): int)|(callable(): string) $either
 */
function callableForms(callable $unknown, callable $either, mixed $mixed): void
{
    $provider = new ScopedCallbackProvider();
    assertType('int', Service::useInheritedValues(true, [$provider, 'result']));
    assertType('uppercase-string', Service::useInheritedValues(true, 'strtoupper'));
    assertType('int', Service::useInheritedValues(true, $provider->result(...)));
    assertType('bool', Service::useInheritedValues(true, $provider));
    assertType('mixed', Service::useInheritedValues(true, $unknown));
    assertType('mixed', Service::useInheritedValues(true, $mixed));
    assertType('mixed', Service::useInheritedValues(true, 42));
    assertType('int|string', Service::useInheritedValues(true, $either));
    assertType('42', ScopedService::useInheritedValues(true, static fn (): int => 42));
    assertType('string', Service::useInheritedValues(true, static fn (string $value): string => $value, ['argument']));
    assertType('string', Service::useInheritedValues(fnArgs: ['argument'], fn: static fn (string $value): string => $value, inheritValues: true));
    assertType('null', Service::useInheritedValues(true, static function (): void {
    }));
    assertType("'result'", Service::useInheritedValues(true, static function (): string {
        return 'result';
    }));
}

function neverResult(): void
{
    Service::useInheritedValues(true, static function (): never {
        throw new RuntimeException();
    });
    assertType('*NEVER*', 42);
}

function genericCallback(ScopedCallbackProvider $provider): void
{
    assertType('mixed', Service::useInheritedValues(true, $provider->identity(...), [42]));
}
