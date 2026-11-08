<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests;

use Composer\InstalledVersions;

/**
 * The committed fixture set (generated classes and class definitions) matching the installed Pimcore line.
 */
final class Fixtures
{
    private function __construct()
    {
    }

    public static function classesDirectory(): string
    {
        return __DIR__ . '/fixtures/classes/' . self::line();
    }

    public static function definitionsDirectory(): string
    {
        return __DIR__ . '/fixtures/definitions/' . self::line();
    }

    /**
     * The installed Pimcore line, as named in the fixture directories: `12.3` or `2026`.
     */
    public static function line(): string
    {
        return str_starts_with(InstalledVersions::getVersion('pimcore/pimcore') ?? '', '12.') ? '12.3' : '2026';
    }
}
