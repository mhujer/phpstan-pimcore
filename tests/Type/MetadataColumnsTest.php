<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class MetadataColumnsTest extends TestCase
{
    public function testUnknownColumnsAndRealMethodSignatures(): void
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
                        - %1$s/tests/Type/calls/relation-metadata.php
                    tmpDir: cache
                    bootstrapFiles:
                        - %1$s/tests/bootstrap.php
                NEON, Phpstan::root(), $definitionConfig));

            $errors = Phpstan::analyse($project, true);
            self::assertSame($definitions ? [19, 20, 21, 22, 23, 24, 30, 31, 35] : [19, 20, 21, 22, 23, 24], array_column($errors, 'line'));
            self::assertSame($definitions
                ? ['method.notFound', 'method.notFound', 'arguments.count', 'argument.type', 'argument.type', 'argument.type', 'method.notFound', 'method.notFound', 'method.notFound']
                : ['method.notFound', 'method.notFound', 'arguments.count', 'argument.type', 'argument.type', 'argument.type'], array_column($errors, 'identifier'));
            self::assertStringContainsString('Call to an undefined method', $errors[0]['message']);
            self::assertStringContainsString('Call to an undefined method', $errors[1]['message']);
            self::assertStringContainsString('invoked with 0 parameters, 1 required', $errors[2]['message']);
            self::assertStringContainsString('expects array', $errors[3]['message']);
            self::assertStringContainsString('expects Pimcore\\Model\\DataObject\\Concrete|null', $errors[4]['message']);
            self::assertStringContainsString('expects Pimcore\\Model\\Element\\ElementInterface|null', $errors[5]['message']);
        }
    }
}
