<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ClassOverrideRegistrationRule> */
final class ClassOverrideRegistrationRuleTest extends RuleTestCase
{
    public function testBypassedConstructions(): void
    {
        $this->analyse([__DIR__ . '/data/override-constructions.php'], [
            ['Construction of Pimcore\\Model\\DataObject\\Product bypasses its registered class override.', 10],
            ['Construction of Pimcore\\Model\\DataObject\\Product bypasses its registered class override.', 11],
            ['Construction of Pimcore\\Model\\DataObject\\Product\\Listing bypasses its registered class override.', 12],
        ]);
    }

    public function testUnregisteredStaticLoadsAndOptOuts(): void
    {
        $this->analyse([__DIR__ . '/data/override-loads.php'], [
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 12],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 13],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 14],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 15],
            ['Static load on App\\Model\\Product does not use a registered class override.', 16],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 23],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 24],
        ]);
    }

    public function testDiagnosticIdentifiers(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/override-constructions.php', __DIR__ . '/data/override-loads.php']);
        $identifiers = array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors)));
        sort($identifiers);
        self::assertSame(['pimcore.bypassedClassOverride', 'pimcore.unregisteredClassOverride'], $identifiers);
    }

    public function testLateStaticAndClassStringCalls(): void
    {
        $this->analyse([__DIR__ . '/data/override-dynamic.php', __DIR__ . '/../Type/Fixtures/OverrideConstructions.php'], [
            ['Construction of Pimcore\\Model\\DataObject\\Product bypasses its registered class override.', 12],
            ['Construction of Pimcore\\Model\\DataObject\\Product bypasses its registered class override.', 13],
            ['Static load on App\\Model\\ChildProduct does not use a registered class override.', 17],
        ]);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassOverrideRegistrationRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', __DIR__ . '/../Type/container-overrides/config.neon'];
    }
}
