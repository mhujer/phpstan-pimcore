<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ClassOverrideRegistrationRule> */
final class GeneratedXmlClassOverrideRegistrationRuleTest extends RuleTestCase
{
    public function testInstalledContainerShape(): void
    {
        $this->analyse([__DIR__ . '/data/override-generated.php'], [
            ['Construction of Pimcore\\Model\\DataObject\\Product bypasses its registered class override.', 8],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 10],
        ]);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassOverrideRegistrationRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $config = sys_get_temp_dir() . '/phpstan-pimcore-rule-generated-overrides.neon';
        file_put_contents($config, sprintf("parameters:\n    pimcore:\n        containerXmlPath: '%s'\n", __DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml'));

        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', $config];
    }
}
