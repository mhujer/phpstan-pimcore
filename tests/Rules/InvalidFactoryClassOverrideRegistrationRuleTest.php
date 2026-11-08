<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ClassOverrideRegistrationRule> */
final class InvalidFactoryClassOverrideRegistrationRuleTest extends RuleTestCase
{
    public function testInvalidListingAndDocumentAssetOverrides(): void
    {
        $this->analyse([__DIR__ . '/data/override-invalid-factories.php'], [
            ['Invalid class override for Pimcore\\Model\\DataObject\\Product\\Listing: the registered class must exist and extend it.', 9],
            ['Invalid class override for Pimcore\\Model\\DataObject\\Product\\Listing: the registered class must exist and extend it.', 10],
            ['Invalid class override for Pimcore\\Model\\Document\\Page: the registered class must exist and extend it.', 11],
            ['Invalid class override for Pimcore\\Model\\Asset\\Image: the registered class must exist and extend it.', 12],
        ]);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassOverrideRegistrationRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', __DIR__ . '/override-invalid-factories.neon'];
    }
}
