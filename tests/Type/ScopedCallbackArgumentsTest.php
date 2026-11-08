<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class ScopedCallbackArgumentsTest extends TestCase
{
    public function testCallbacksAcceptParametersAndVoidResultsWithoutExtraDiagnostics(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %1$s/extension.neon
            parameters:
                level: max
                paths:
                    - %1$s/tests/Type/calls/scoped-callbacks.php
                tmpDir: cache
            NEON, Phpstan::root()));

        self::assertSame([], Phpstan::analyse($project));
    }
}
