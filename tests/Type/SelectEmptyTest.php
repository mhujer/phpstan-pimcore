<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class SelectEmptyTest extends TestCase
{
    public function testEmptyChecksAreNotAlwaysTrue(): void
    {
        foreach ([false, true] as $mandatory) {
            $project = Phpstan::createProject();
            $definition = file_get_contents(Fixtures::definitionsDirectory() . '/definition_Product.php');
            self::assertIsString($definition);
            if ($mandatory) {
                $definition = str_replace("'mandatory' => false", "'mandatory' => true", $definition);
            }

            mkdir($project . '/definitions');
            file_put_contents($project . '/definitions/definition_Product.php', $definition);
            file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
                includes:
                    - %1$s/extension.neon
                parameters:
                    level: max
                    paths:
                        - %1$s/tests/Type/calls/select-empty.php
                    tmpDir: cache
                    bootstrapFiles:
                        - %1$s/tests/bootstrap.php
                    pimcore:
                        classDefinitionDirectory: '%2$s/definitions'
                NEON, Phpstan::root(), $project));

            self::assertSame([], Phpstan::analyse($project, true), $mandatory ? 'Mandatory selects' : 'Optional selects');
        }
    }
}
