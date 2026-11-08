<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\LanguageArgumentRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<LanguageArgumentRule> */
final class LanguageArgumentWithoutXmlRuleTest extends RuleTestCase
{
    public function testDefaultRuleIsSilentWithoutContainerXml(): void
    {
        $this->analyse([__DIR__ . '/data/language-arguments.php'], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(LanguageArgumentRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
