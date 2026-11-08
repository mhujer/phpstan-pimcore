<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ClassOverrideRegistrationRule> */
final class InvalidClassOverrideRegistrationRuleTest extends RuleTestCase
{
    public function testInvalidEntriesAtFactoryRelationAndListingSites(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/override-invalid.php']);
        self::assertSame([10, 11, 12, 13, 14, 15, 20, 21, 22, 23, 25, 27, 39, 40, 41, 42, 45], array_map(static fn (Error $error): ?int => $error->getLine(), $errors));
        foreach ($errors as $error) {
            self::assertSame('pimcore.invalidClassOverride', $error->getIdentifier());
            self::assertStringContainsString('Invalid class override for Pimcore\\Model\\DataObject\\', $error->getMessage());
        }
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassOverrideRegistrationRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $config = sys_get_temp_dir() . '/phpstan-pimcore-rule-overrides-definitions.neon';
        file_put_contents($config, sprintf("parameters:\n    pimcore:\n        classDefinitionDirectory: '%s'\n", Fixtures::definitionsDirectory()));

        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', __DIR__ . '/../Type/container-overrides/invalid.neon', $config];
    }
}
