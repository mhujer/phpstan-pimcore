<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class WorkflowResultCacheTest extends TestCase
{
    public function testWorkflowWiringAndReferencedTransitionsInvalidateCachedDiagnostics(): void
    {
        $project = Phpstan::createProject();
        mkdir($project . '/src');
        file_put_contents($project . '/src/check.php', <<<'PHP'
            <?php
            function workflows(\Symfony\Component\Workflow\Registry $registry, \Symfony\Component\Workflow\WorkflowInterface $workflow, \Symfony\Component\Workflow\Marking $marking, object $subject): void {
                $registry->get($subject, 'product_editorial');
                $workflow->can($subject, 'publish');
                $marking->has('draft');
            }
            PHP);
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
                - %s/rules.neon
            parameters:
                level: 0
                paths:
                    - src
                tmpDir: cache
                pimcore:
                    containerXmlPath: container.xml
            NEON, Phpstan::root(), Phpstan::root()));
        $xml = file_get_contents(__DIR__ . '/../fixtures/container/workflows.xml');
        self::assertIsString($xml);
        $xml = str_replace('</services>', '<service id="release.service" class="Symfony\Component\Workflow\Transition"><argument>release</argument></service></services>', $xml);
        file_put_contents($project . '/container.xml', $xml);
        self::assertSame([], Phpstan::analyse($project));
        self::assertSame([], Phpstan::analyse($project));

        // Only the alias changes: its new target must replace the cached transition name.
        file_put_contents($project . '/container.xml', str_replace('alias="publish.service"', 'alias="release.service"', $xml));
        self::assertSame(['pimcore.unknownWorkflowTransition'], array_column(Phpstan::analyse($project, true), 'identifier'));

        // The Definition service does not change: only a referenced Transition does.
        file_put_contents($project . '/container.xml', str_replace('>publish<', '>release<', $xml));
        self::assertSame(['pimcore.unknownWorkflowTransition'], array_column(Phpstan::analyse($project, true), 'identifier'));
        file_put_contents($project . '/container.xml', str_replace('>product_editorial<', '>editorial<', $xml));
        self::assertSame(['pimcore.unknownWorkflow'], array_column(Phpstan::analyse($project, true), 'identifier'));
        file_put_contents($project . '/container.xml', str_replace('>draft<', '>initial<', $xml));
        self::assertSame(['pimcore.unknownWorkflowPlace'], array_column(Phpstan::analyse($project, true), 'identifier'));

        // An incomplete extra definition disables checks; removing it enables them again.
        $invalid = str_replace('</services>', '<service id="broken.definition" class="Symfony\Component\Workflow\Definition"/></services>', $xml);
        $unknown = str_replace('>publish<', '>release<', $xml);
        file_put_contents($project . '/container.xml', str_replace('>publish<', '>release<', $invalid));
        self::assertSame([], Phpstan::analyse($project));
        file_put_contents($project . '/container.xml', $unknown);
        self::assertSame(['pimcore.unknownWorkflowTransition'], array_column(Phpstan::analyse($project, true), 'identifier'));
    }
}
