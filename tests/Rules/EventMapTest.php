<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Composer\InstalledVersions;
use Mhujer\PHPStan\Pimcore\Rules\EventMap;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

final class EventMapTest extends TestCase
{
    public function testEveryAnnotatedInstalledPimcoreConstantIsShipped(): void
    {
        $root = InstalledVersions::getInstallPath('pimcore/pimcore');
        self::assertNotNull($root);
        $corrections = require __DIR__ . '/../../tools/event-map/corrections.php';
        self::assertIsArray($corrections);
        $checked = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || !str_ends_with($file->getFilename(), 'Events.php')) {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            self::assertIsString($source);
            if (!preg_match('/namespace\s+([^;]+);/', $source, $namespace)) {
                continue;
            }
            $class = trim($namespace[1]) . '\\' . $file->getBasename('.php');
            self::assertTrue(class_exists($class), $class);
            foreach ((new ReflectionClass($class))->getReflectionConstants() as $constant) {
                if (!preg_match('/@Event\("([^"]+)"\)/', $constant->getDocComment() ?: '', $annotation)) {
                    continue;
                }
                $name = $constant->getValue();
                self::assertIsString($name);
                self::assertArrayHasKey($name, EventMap::EVENTS);
                self::assertSame($corrections[$name] ?? ltrim($annotation[1], '\\'), EventMap::EVENTS[$name], $class . '::' . $constant->getName());
                $checked++;
            }
        }
        self::assertGreaterThan(100, $checked);
    }

    public function testDispatchSiteCorrectionsAndBothSupportedLinesAreShipped(): void
    {
        self::assertSame('Pimcore\\Event\\Model\\DataObject\\ClassDefinition\\UrlSlugEvent', EventMap::EVENTS['pimcore.model.dataobject.classdefinition.urlslug.postsave']);
        self::assertSame('Pimcore\\Event\\Model\\MailEvent', EventMap::EVENTS['pimcore.mail.preLog']);
        self::assertSame('Pimcore\\Bundle\\InstallBundle\\Event\\InstallerStepEvent', EventMap::EVENTS['pimcore.installer.step']);
        self::assertSame('Pimcore\\Bundle\\InstallBundle\\Event\\BundleSetupEvent', EventMap::EVENTS['pimcore.installer.setup_bundles']);
        self::assertSame('Pimcore\\Event\\Model\\DataObjectDeleteInfoEvent', EventMap::EVENTS['pimcore.dataobject.deleteInfo']);
    }
}
