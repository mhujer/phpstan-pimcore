<?php

declare(strict_types=1);

namespace ScopedCoauthorCallbacks;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\ScopedCallbackProvider;
use Pimcore\Model\Version\CoauthorContext;
use Pimcore\Model\Version\CoauthorContextInterface;
use RuntimeException;
use function PHPStan\Testing\assertType;

function contextResults(CoauthorContextInterface $context, CoauthorContext $implementation): void
{
    assertType('42', $context->withCoauthor('automation', 'importer', static fn (): int => 42));
    assertType('42', $implementation->withCoauthor('automation', 'importer', static fn (): int => 42));
    assertType('null', $context->withCoauthor('automation', 'importer', static function (): void {
    }));
    assertType('42', $context->withCoauthor(callback: static fn (): int => 42, coauthor: 'importer', type: 'automation'));
}

/**
 * @param (callable(): int)|(callable(): string) $either
 */
function callableForms(CoauthorContextInterface $context, ScopedCallbackProvider $provider, callable $unknown, callable $either, mixed $mixed): void
{
    assertType('int', $context->withCoauthor('automation', 'importer', [$provider, 'result']));
    assertType('uppercase-string', $context->withCoauthor('automation', 'importer', 'strtoupper'));
    assertType('int', $context->withCoauthor('automation', 'importer', $provider->result(...)));
    assertType('bool', $context->withCoauthor('automation', 'importer', $provider));
    assertType('mixed', $context->withCoauthor('automation', 'importer', $unknown));
    assertType('mixed', $context->withCoauthor('automation', 'importer', $mixed));
    assertType('mixed', $context->withCoauthor('automation', 'importer', 42));
    assertType('int|string', $context->withCoauthor('automation', 'importer', $either));
    assertType("'result'", $context->withCoauthor('automation', 'importer', static function (): string {
        return 'result';
    }));
}

function neverResult(CoauthorContextInterface $context): void
{
    $context->withCoauthor('automation', 'importer', static function (): never {
        throw new RuntimeException();
    });
    assertType('*NEVER*', 42);
}
