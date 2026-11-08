<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Rules\WorkflowNamesRule;
use Mhujer\PHPStan\Pimcore\Tests\ContainerXml\ContainerXmlReaderTest;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** @extends RuleTestCase<WorkflowNamesRule> */
final class WorkflowNamesRuleTest extends RuleTestCase
{
    private ?ContainerXmlReader $reader = null;

    protected function getRule(): Rule
    {
        return new WorkflowNamesRule($this->reader ?? new ContainerXmlReader(__DIR__ . '/../fixtures/container/workflows.xml'));
    }

    public function testChecksAllDefinitionsAndUsesWiredWorkflowNames(): void
    {
        $this->analyse([__DIR__ . '/data/workflow-names.php'], [
            ["Workflow 'missing' is not defined in any workflow.", 13],
            ["Workflow 'missing' is not defined in any workflow.", 14],
            ["Transition 'missing' is not defined in any workflow.", 15],
            ["Transition 'missing' is not defined in any workflow.", 16],
            ["Place 'missing' is not defined in any workflow.", 17],
        ]);
    }

    public function testInstalledGeneratedXmlShape(): void
    {
        $this->reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');
        self::assertSame('product_editorial', $this->reader->workflowServices()['workflow.product_editorial']['arguments'][3]);
        $this->analyse([__DIR__ . '/data/workflow-names.php'], [
            ["Workflow 'missing' is not defined in any workflow.", 13],
            ["Workflow 'missing' is not defined in any workflow.", 14],
            ["Transition 'missing' is not defined in any workflow.", 15],
            ["Transition 'missing' is not defined in any workflow.", 16],
            ["Place 'missing' is not defined in any workflow.", 17],
            ["Workflow 'translation_job_default' is not defined in any workflow.", 19],
            ["Transition 'translate' is not defined in any workflow.", 20],
            ["Place 'translated' is not defined in any workflow.", 21],
        ]);
    }

    public function testWithoutXmlIsSilent(): void
    {
        $this->reader = new ContainerXmlReader(null);
        $this->analyse([__DIR__ . '/data/workflow-names.php'], []);
    }

    public function testIdentifiers(): void
    {
        self::assertSame([
            'pimcore.unknownWorkflow',
            'pimcore.unknownWorkflow',
            'pimcore.unknownWorkflowTransition',
            'pimcore.unknownWorkflowTransition',
            'pimcore.unknownWorkflowPlace',
        ], array_map(static fn (Error $error): ?string => $error->getIdentifier(), $this->gatherAnalyserErrors([__DIR__ . '/data/workflow-names.php'])));
    }

    #[DataProvider('unparseableDefinitions')]
    public function testOneUnparseableDefinitionSilencesEveryCheck(string $search, string $replacement): void
    {
        $xml = file_get_contents(__DIR__ . '/../fixtures/container/workflows.xml');
        self::assertIsString($xml);
        $this->reader = new ContainerXmlReader(ContainerXmlReaderTest::writeXml(str_replace($search, $replacement, $xml)));
        $this->analyse([__DIR__ . '/data/workflow-names.php'], []);
    }

    /** @return array<string, array{string, string}> */
    public static function unparseableDefinitions(): array
    {
        return [
            'unresolved place' => ['>translated<', '>%env(PLACE)%<'],
            'missing places' => ['<argument type="collection"><argument>translated</argument></argument>', '<argument>null</argument>'],
            'missing transition service' => ['id="publish.alias"/>', 'id="absent"/>'],
            'transition factory' => ['<argument>publish</argument></service>', '<argument>publish</argument><factory service="builder" method="make"/></service>'],
            'definition factory' => ['<service id="bundle.definition" class="Symfony\\Component\\Workflow\\Definition">', '<service id="bundle.definition" class="Symfony\\Component\\Workflow\\Definition"><factory service="builder" method="make"/>'],
            'null workflow name' => ['>translation_job_default<', '>null<'],
            'unresolved workflow name' => ['>translation_job_default<', '>%env(WORKFLOW)%<'],
            'unwired definition' => ['id="bundle.definition"/>', 'id="absent.definition"/>'],
            'transition alias cycle' => ['alias="publish.service"', 'alias="publish.alias"'],
        ];
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
