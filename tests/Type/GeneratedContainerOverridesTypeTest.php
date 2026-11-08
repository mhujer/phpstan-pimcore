<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GeneratedContainerOverridesTypeTest extends TypeInferenceTestCase
{
    /** @return iterable<mixed> */
    public static function dataFileAsserts(): iterable
    {
        yield from self::gatherAssertTypes(__DIR__ . '/calls/generated-container-overrides.php');
    }

    #[DataProvider('dataFileAsserts')]
    public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
    {
        $this->assertFileAsserts($assertType, $file, ...$args);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $config = sys_get_temp_dir() . '/phpstan-pimcore-generated-overrides.neon';
        file_put_contents($config, sprintf("parameters:\n    pimcore:\n        containerXmlPath: '%s'\n", __DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml'));

        return [__DIR__ . '/../../extension.neon', $config];
    }
}
