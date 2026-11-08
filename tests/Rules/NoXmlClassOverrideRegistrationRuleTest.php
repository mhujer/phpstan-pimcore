<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ClassOverrideRegistrationRule> */
final class NoXmlClassOverrideRegistrationRuleTest extends RuleTestCase
{
    public function testRegistrationChecksAreSilentWithoutXml(): void
    {
        $this->analyse([
            __DIR__ . '/data/override-constructions.php',
            __DIR__ . '/data/override-loads.php',
            __DIR__ . '/data/override-invalid.php',
            __DIR__ . '/data/override-dynamic.php',
            __DIR__ . '/../Type/Fixtures/OverrideConstructions.php',
        ], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassOverrideRegistrationRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
