<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;

/**
 * Type items that read class definitions, with the fixture definitions configured. Without them, the same items are
 * covered by GeneratedClassesTypeTest.
 */
final class ClassDefinitionsTypeTest extends ClassDefinitionsTypeTestCase
{
    protected static function dataDirectory(): string
    {
        return __DIR__ . '/data-with-definitions';
    }

    protected static function definitionsDirectory(): string
    {
        return Fixtures::definitionsDirectory();
    }
}
