<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\WorkflowNamesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<WorkflowNamesRule> */
final class WorkflowNamesWithoutXmlRuleTest extends RuleTestCase
{
    public function testDefaultRuleIsSilentWithoutContainerXml(): void
    {
        $this->analyse([__DIR__ . '/data/workflow-names.php'], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(WorkflowNamesRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
