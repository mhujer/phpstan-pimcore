<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class DiagnoseTest extends TestCase
{
    public function testAnalysisRejectsInvalidExplicitXmlBeforeAnyFeatureReadsIt(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/container.xml', '<container>SECRET');
        file_put_contents($project . '/analysed.php', '<?php');
        file_put_contents($project . '/phpstan.neon', "includes:\n    - " . Phpstan::root() . "/extension.neon\nparameters:\n    level: 0\n    paths: [analysed.php]\n    pimcore:\n        containerXmlPath: container.xml\n");
        exec('cd ' . escapeshellarg($project) . ' && ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(Phpstan::root() . '/vendor/bin/phpstan') . ' analyse --no-progress -c phpstan.neon 2>&1', $output, $status);
        $message = implode("\n", $output);

        self::assertNotSame(0, $status);
        self::assertStringContainsString('CannotreadPimcoreContainerXML"' . $project . '/container.xml".', str_replace(["\n", "\r", ' '], '', $message));
        self::assertStringNotContainsString('SECRET', $message);
    }

    public function testDiagnoseReportsTheContainerXmlSource(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', "includes:\n    - " . Phpstan::root() . "/extension.neon\nparameters:\n    level: 0\n");
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(Phpstan::root() . '/vendor/bin/phpstan') . ' diagnose -c ' . escapeshellarg($project . '/phpstan.neon') . ' 2>&1', $output, $status);

        self::assertSame(0, $status, implode("\n", $output));
        self::assertStringContainsString('Pimcore Container XML: none (source: none).', implode("\n", $output));
    }
}
