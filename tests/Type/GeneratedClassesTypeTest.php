<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GeneratedClassesTypeTest extends TypeInferenceTestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function dataFileAsserts(): iterable
    {
        // data/<line>/ holds what differs between the Pimcore lines.
        $files = [...glob(__DIR__ . '/data/*.php') ?: [], ...glob(__DIR__ . '/data/' . Fixtures::line() . '/*.php') ?: []];
        $files[] = __DIR__ . '/Fixtures/OverrideCalls.php';
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
        return [__DIR__ . '/../../extension.neon'];
    }
}
