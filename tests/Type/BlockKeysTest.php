<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class BlockKeysTest extends TestCase
{
    public function testUnknownKeysNeedDefinitions(): void
    {
        foreach ([false, true] as $definitions) {
            $project = Phpstan::createProject();
            $definitionConfig = $definitions ? sprintf("    pimcore:\n        classDefinitionDirectory: '%s'\n", Fixtures::definitionsDirectory()) : '';
            file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
                includes:
                    - %1$s/extension.neon
                parameters:
                %2$s
                    level: max
                    paths:
                        - %1$s/tests/Type/calls/blocks.php
                    tmpDir: cache
                    bootstrapFiles:
                        - %1$s/tests/bootstrap.php
                NEON, Phpstan::root(), $definitionConfig));

            $errors = Phpstan::analyse($project, true);
            self::assertSame($definitions ? [17] : [], array_column($errors, 'line'));
            self::assertSame($definitions ? ['offsetAccess.notFound'] : [], array_column($errors, 'identifier'));
        }
    }
}
