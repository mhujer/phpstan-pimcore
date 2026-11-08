<?php

declare(strict_types=1);

/**
 * Builds and compiles a synthetic Pimcore container without booting the kernel or connecting to a DB.
 * Usage: php generate-container.php <vendor-dir> <output-file>
 */

use Pimcore\Bootstrap;
use Pimcore\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php generate-container.php <vendor-dir> <output-file>\n");
    exit(1);
}
[, $vendorDir, $outputFile] = $argv;
require $vendorDir . '/autoload.php';
require __DIR__ . '/project/src/Listeners.php';

define('PIMCORE_PROJECT_ROOT', __DIR__ . '/project');
Bootstrap::defineConstants();
final class FixtureKernel extends Kernel
{
    public function buildOnly(): ContainerBuilder
    {
        $this->initializeBundles();
        $container = $this->buildContainer();
        $container->compile();

        return $container;
    }
}
$container = (new FixtureKernel('dev', true))->buildOnly();
$xml = file_get_contents($container->getParameter('debug.container.dump'));
if (!is_dir(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0775, true);
}
// Keep the real compiled shape while removing machine-specific absolute paths.
$xml = str_replace(dirname(__DIR__, 2), '/fixture', $xml);
file_put_contents($outputFile, $xml);
echo $outputFile, "\n";
