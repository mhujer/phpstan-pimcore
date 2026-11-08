<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Type inference over a data directory (plus its subdirectory for the installed Pimcore line), with `classDefinitionDirectory` set to a definitions directory.
 */
abstract class ClassDefinitionsTypeTestCase extends TypeInferenceTestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        // <data directory>/<line>/ holds what differs between the Pimcore lines.
        $directory = static::dataDirectory();
        $files = [...glob($directory . '/*.php') ?: [], ...glob($directory . '/' . Fixtures::line() . '/*.php') ?: []];
        foreach ($files as $file) {
            yield from self::gatherAssertTypes($file);
        }
    }

    #[DataProvider('dataFileAsserts')]
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        // The definitions directory may depend on the installed Pimcore line, so the config is written on the fly.
        $config = sys_get_temp_dir() . '/phpstan-pimcore-tests/class-definitions-' . md5(static::definitionsDirectory()) . '.neon';
        if (!is_dir(dirname($config))) {
            mkdir(dirname($config), 0o777, true);
        }

        file_put_contents($config, sprintf(
            "parameters:\n    pimcore:\n        classDefinitionDirectory: '%s'\n",
            static::definitionsDirectory(),
        ));

        return [__DIR__ . '/../../extension.neon', $config];
    }

    abstract protected static function dataDirectory(): string;

    abstract protected static function definitionsDirectory(): string;
}
