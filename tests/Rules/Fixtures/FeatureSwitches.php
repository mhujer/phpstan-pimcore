<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

/**
 * A project's own toggle, added through `additionalToggles` in toggle-not-restored.neon.
 */
final class FeatureSwitches
{
    private static bool $indexing = true;

    public static function disableIndexing(): void
    {
        self::$indexing = false;
    }

    public static function enableIndexing(): void
    {
        self::$indexing = true;
    }

    public static function isIndexing(): bool
    {
        return self::$indexing;
    }
}
