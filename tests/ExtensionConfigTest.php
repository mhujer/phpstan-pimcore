<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Rules\CacheKeyMismatchRule;
use Mhujer\PHPStan\Pimcore\Rules\CacheKeyRule;
use Mhujer\PHPStan\Pimcore\Rules\ClassOverrideRegistrationRule;
use Mhujer\PHPStan\Pimcore\Rules\ConditionPlaceholdersRule;
use Mhujer\PHPStan\Pimcore\Rules\ElementLookupArgumentsRule;
use Mhujer\PHPStan\Pimcore\Rules\InterpolatedConditionRule;
use Mhujer\PHPStan\Pimcore\Rules\InvalidElementKeyRule;
use Mhujer\PHPStan\Pimcore\Rules\LanguageArgumentRule;
use Mhujer\PHPStan\Pimcore\Rules\ListenerWiringRule;
use Mhujer\PHPStan\Pimcore\Rules\ListingArgumentsRule;
use Mhujer\PHPStan\Pimcore\Rules\SelectOptionRule;
use Mhujer\PHPStan\Pimcore\Rules\ToggleNotRestoredRule;
use Mhujer\PHPStan\Pimcore\Rules\UnknownDefinitionNameRule;
use Mhujer\PHPStan\Pimcore\Rules\UnknownFieldNameRule;
use Mhujer\PHPStan\Pimcore\Rules\UnknownValuesKeyRule;
use Mhujer\PHPStan\Pimcore\Rules\WorkflowNamesRule;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Throwable;

final class ExtensionConfigTest extends TestCase
{
    private const string ROOT = __DIR__ . '/..';

    public function testBothConfigFilesLoadWithDefaultParameters(): void
    {
        $container = self::createContainer([]);

        self::assertSame(
            [
                'classDefinitionDirectory' => null,
                'containerXmlPath' => null,
                'additionalToggles' => [],
            ],
            $container->getParameter('pimcore'),
        );
    }

    public function testMissingExplicitContainerXmlIsAConfigError(): void
    {
        $this->expectExceptionMessageMatches('~file exists.+pimcore.+containerXmlPath~');
        self::createContainer([self::writeUserConfig("parameters:\n    pimcore:\n        containerXmlPath: missing.xml\n")]);
    }

    public function testRelativeContainerXmlPathIsExpandedAndReaderIsRegistered(): void
    {
        $config = self::writeUserConfig("parameters:\n    pimcore:\n        containerXmlPath: container.xml\n");
        file_put_contents(dirname($config) . '/container.xml', '<container><parameters><parameter key="pimcore.config" type="collection"/></parameters></container>');
        $container = self::createContainer([$config]);
        // Schema validation already parsed the XML; runtime consumers must reuse that snapshot.
        unlink(dirname($config) . '/container.xml');
        $reader = $container->getByType(ContainerXmlReader::class);

        self::assertStringContainsString('"' . dirname($config) . '/container.xml" (source: own)', $reader->diagnosis());
    }

    public function testBorrowedContainerXmlIsReadAtRuntime(): void
    {
        $config = self::writeUserConfig(<<<'NEON'
            parameters:
                symfony:
                    containerXmlPath: borrowed.xml
            parametersSchema:
                symfony: structure([containerXmlPath: string()])
            NEON);
        file_put_contents(dirname($config) . '/borrowed.xml', '<container><parameters><parameter key="pimcore.config" type="collection"><parameter key="known">borrowed</parameter></parameter></parameters></container>');
        $reader = self::createContainer([$config])->getByType(ContainerXmlReader::class);

        self::assertSame('borrowed', $reader->config(['known']));
        self::assertStringContainsString('source: symfony', $reader->diagnosis());
    }

    public function testOwnContainerXmlTakesPrecedenceOverBorrowedPath(): void
    {
        $config = self::writeUserConfig(<<<'NEON'
            parameters:
                pimcore:
                    containerXmlPath: own.xml
                symfony:
                    containerXmlPath: missing.xml
            parametersSchema:
                symfony: structure([containerXmlPath: string()])
            NEON);
        file_put_contents(dirname($config) . '/own.xml', '<container><parameters><parameter key="pimcore.config" type="collection"/></parameters></container>');
        $reader = self::createContainer([$config])->getByType(ContainerXmlReader::class);

        self::assertSame([], $reader->config([]));
        self::assertStringContainsString('source: own', $reader->diagnosis());
    }

    public function testMalformedBorrowedContainerXmlIsDisabled(): void
    {
        $config = self::writeUserConfig(<<<'NEON'
            parameters:
                symfony:
                    containerXmlPath: borrowed.xml
            parametersSchema:
                symfony: structure([containerXmlPath: string()])
            NEON);
        file_put_contents(dirname($config) . '/borrowed.xml', '<container>SECRET');
        $reader = self::createContainer([$config])->getByType(ContainerXmlReader::class);

        self::assertNull($reader->config([]));
        self::assertStringContainsString('disabled: XML cannot be parsed', $reader->diagnosis());
        self::assertStringNotContainsString('SECRET', $reader->diagnosis());
    }

    public function testNonPimcoreBorrowedContainerXmlIsDisabled(): void
    {
        $config = self::writeUserConfig(<<<'NEON'
            parameters:
                symfony:
                    containerXmlPath: borrowed.xml
            parametersSchema:
                symfony: structure([containerXmlPath: string()])
            NEON);
        file_put_contents(dirname($config) . '/borrowed.xml', '<container><parameters><parameter key="secret">SECRET</parameter></parameters></container>');
        $reader = self::createContainer([$config])->getByType(ContainerXmlReader::class);

        self::assertNull($reader->config([]));
        self::assertStringContainsString('disabled: missing "pimcore.config"', $reader->diagnosis());
    }

    public function testDefaultRulesAreRegistered(): void
    {
        $rules = self::createContainer([])->getServicesByTag('phpstan.rules.rule');

        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof WorkflowNamesRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof LanguageArgumentRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof SelectOptionRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ClassOverrideRegistrationRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof CacheKeyRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof CacheKeyMismatchRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ToggleNotRestoredRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof UnknownFieldNameRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof UnknownDefinitionNameRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof InterpolatedConditionRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ElementLookupArgumentsRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof InvalidElementKeyRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ListenerWiringRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ConditionPlaceholdersRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof ListingArgumentsRule));
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof UnknownValuesKeyRule));
    }

    public function testXmlOnlyRulesAreSilentWithDefaultConfiguration(): void
    {
        $project = Phpstan::createProject();
        $root = Phpstan::root();
        file_put_contents($project . '/phpstan.neon', <<<NEON
            includes:
                - $root/extension.neon
                - $root/rules.neon
            parameters:
                customRulesetUsed: true
                bootstrapFiles: [$root/tests/bootstrap.php]
                paths:
                    - $root/tests/Rules/data/language-arguments.php
                    - $root/tests/Rules/data/workflow-names.php
                    - $root/tests/Rules/data/override-loads.php
                    - $root/tests/Rules/data/override-constructions.php
                    - $root/tests/Rules/data/override-invalid.php
                    - $root/tests/Rules/data/listener-xml.php
            NEON);
        exec('cd ' . escapeshellarg($project) . ' && ' . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/vendor/bin/phpstan') . ' analyse --no-progress -c phpstan.neon 2>&1', $output, $status);

        self::assertSame(0, $status, implode("\n", $output));
    }

    public function testInvalidParameterValueIsRejectedBySchema(): void
    {
        $this->expectExceptionMessageMatches('~pimcore.+additionalToggles~');

        self::createContainer([self::writeUserConfig(<<<'NEON'
            parameters:
                pimcore:
                    additionalToggles: 'yes'
            NEON)]);
    }

    public function testRelativeClassDefinitionDirectoryIsExpandedAgainstTheConfigFile(): void
    {
        $userConfig = self::writeUserConfig(<<<'NEON'
            parameters:
                pimcore:
                    classDefinitionDirectory: var/classes
            NEON);
        mkdir(dirname($userConfig) . '/var/classes', 0o777, true);

        $parameters = self::createContainer([$userConfig])->getParameter('pimcore');

        self::assertIsArray($parameters);
        self::assertSame(dirname($userConfig) . '/var/classes', $parameters['classDefinitionDirectory']);
    }

    public function testExplicitClassDefinitionDirectoryThatDoesNotExistIsAConfigError(): void
    {
        $userConfig = self::writeUserConfig(<<<'NEON'
            parameters:
                pimcore:
                    classDefinitionDirectory: does-not-exist
            NEON);

        $exception = null;
        try {
            self::createContainer([$userConfig]);
        } catch (Throwable $exception) {
        }

        // PHPStan prints schema validation errors as "Invalid configuration" and stops. Nette is prefixed in the phar.
        self::assertNotNull($exception);
        self::assertStringEndsWith('Nette\\Schema\\ValidationException', $exception::class);
        self::assertMatchesRegularExpression('~directory exists.+pimcore.+classDefinitionDirectory~', $exception->getMessage());
    }

    public function testExplicitClassDefinitionDirectoryThatExistsIsAccepted(): void
    {
        $userConfig = self::writeUserConfig(<<<'NEON'
            parameters:
                pimcore:
                    classDefinitionDirectory: .
            NEON);

        $parameters = self::createContainer([$userConfig])->getParameter('pimcore');

        self::assertIsArray($parameters);
        self::assertSame(dirname($userConfig), $parameters['classDefinitionDirectory']);
    }

    /**
     * @param list<string> $userConfigFiles
     */
    private static function createContainer(array $userConfigFiles): Container
    {
        return (new ContainerFactory(self::ROOT))->create(
            self::createTempDirectory(),
            [self::ROOT . '/extension.neon', self::ROOT . '/rules.neon', ...$userConfigFiles],
            [],
        );
    }

    private static function writeUserConfig(string $neon): string
    {
        $file = self::createTempDirectory() . '/phpstan.neon';
        file_put_contents($file, $neon);

        return $file;
    }

    private static function createTempDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/phpstan-pimcore-tests/' . bin2hex(random_bytes(8));
        mkdir($directory, 0o777, true);

        return $directory;
    }
}
