<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

/**
 * Class definitions that contradict the generated classes: the generated classes win.
 */
final class ConflictingClassDefinitionsTypeTest extends ClassDefinitionsTypeTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data-with-conflicting-definitions';
    }

    protected static function definitionsDirectory(): string
    {
        return __DIR__ . '/../fixtures/definitions-conflicting';
    }
}
