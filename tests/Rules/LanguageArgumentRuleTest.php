<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Rules\LanguageArgumentRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/** @extends RuleTestCase<LanguageArgumentRule> */
final class LanguageArgumentRuleTest extends RuleTestCase
{
    private ?ContainerXmlReader $reader = null;

    public function testLocalizedGetterChecksExactLanguages(): void
    {
        $this->analyse([__DIR__ . '/data/language-getters.php'], [
            ["Language 'DE' is not configured in general.valid_languages.", 11],
            ["Language 'de-DE' is not configured in general.valid_languages.", 12],
        ]);
    }

    public function testRequiredLanguageArguments(): void
    {
        $this->analyse([__DIR__ . '/data/language-arguments.php'], [
            ["Language 'fr' is not configured in general.valid_languages.", 12],
            ["Language 'fr' is not configured in general.valid_languages.", 13],
            ["Language 'fr' is not configured in general.valid_languages.", 14],
            ["Language 'fr' is not configured in general.valid_languages.", 15],
            ["Language 'fr' is not configured in general.valid_languages.", 16],
            ["Language 'fr' is not configured in general.valid_languages.", 17],
            ["Language 'fr' is not configured in general.valid_languages.", 18],
            ["Language 'fr' is not configured in general.valid_languages.", 19],
            ["Language 'fr' is not configured in general.valid_languages.", 20],
        ]);
    }

    public function testLocalizedBricksAndFieldcollections(): void
    {
        require_once __DIR__ . '/data/language-items.php';
        $this->analyse([__DIR__ . '/data/language-items.php'], [
            ["Language 'fr' is not configured in general.valid_languages.", 52],
            ["Language 'fr' is not configured in general.valid_languages.", 53],
            ["Language 'fr' is not configured in general.valid_languages.", 54],
            ["Language 'fr' is not configured in general.valid_languages.", 55],
            ["Language 'fr' is not configured in general.valid_languages.", 56],
            ["Language 'fr' is not configured in general.valid_languages.", 57],
            ["Language 'fr' is not configured in general.valid_languages.", 58],
            ["Language 'fr' is not configured in general.valid_languages.", 59],
        ]);
    }

    public function testNamedArgumentsAndConstantUnions(): void
    {
        $this->analyse([__DIR__ . '/data/language-inputs.php'], [
            ["Language 'fr' is not configured in general.valid_languages.", 12],
            ["Language 'fr' is not configured in general.valid_languages.", 13],
            ["Language 'fr' is not configured in general.valid_languages.", 14],
            ["Language 'fr' is not configured in general.valid_languages.", 15],
            ["Language 'fr' is not configured in general.valid_languages.", 16],
            ["Language 'fr' is not configured in general.valid_languages.", 17],
            ["Language 'default' is not configured in general.valid_languages.", 18],
            ["Language 'fr' is not configured in general.valid_languages.", 19],
            ["Language 'fr' is not configured in general.valid_languages.", 22],
            ["Language 'fr' is not configured in general.valid_languages.", 23],
        ]);
    }

    public function testWithoutContainerXmlIsSilent(): void
    {
        $this->reader = new ContainerXmlReader(null);
        $this->analyse([__DIR__ . '/data/language-arguments.php'], []);
    }

    #[DataProvider('disabledConfigs')]
    public function testOnlyAnAuthoritativeNonEmptyLanguageListEnablesDiagnostics(string $xml): void
    {
        $this->reader = new ContainerXmlReader(self::writeXml($xml));
        $this->analyse([__DIR__ . '/data/language-arguments.php'], []);
    }

    /** @return array<string, array{string}> */
    public static function disabledConfigs(): array
    {
        $xml = file_get_contents(__DIR__ . '/../fixtures/container/languages.xml');
        self::assertIsString($xml);

        return [
            'settings store' => [str_replace('>symfony-config<', '>settings-store<', $xml)],
            'disabled' => [str_replace('>symfony-config<', '>disabled<', $xml)],
            'unresolved target' => [str_replace('>symfony-config<', '>%env(READ_TARGET)%<', $xml)],
            'missing languages' => [str_replace('key="valid_languages"', 'key="absent_languages"', $xml)],
            'empty languages' => [str_replace('<parameter>en</parameter><parameter>de</parameter><parameter>de_DE</parameter>', '', $xml)],
        ];
    }

    /**
     * Pimcore reads system settings from the container first when no read target is set.
     */
    #[DataProvider('unsetTargetConfigs')]
    public function testUnsetReadTargetReadsTheContainer(string $xml): void
    {
        $this->reader = new ContainerXmlReader(self::writeXml($xml));
        $this->analyse([__DIR__ . '/data/language-getters.php'], [
            ["Language 'DE' is not configured in general.valid_languages.", 11],
            ["Language 'de-DE' is not configured in general.valid_languages.", 12],
        ]);
    }

    /** @return array<string, array{string}> */
    public static function unsetTargetConfigs(): array
    {
        $xml = file_get_contents(__DIR__ . '/../fixtures/container/languages.xml');
        self::assertIsString($xml);

        return [
            'unset target' => [str_replace('>symfony-config<', '>null<', $xml)],
            'missing target' => [str_replace('key="read_target"', 'key="absent_target"', $xml)],
        ];
    }

    public function testInstalledGeneratedContainerShape(): void
    {
        $path = __DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml';
        $this->reader = new ContainerXmlReader($path);
        self::assertSame(['en', 'de'], $this->reader->config(['general', 'valid_languages']));
        self::assertNull($this->reader->config(['config_location', 'system_settings', 'read_target', 'type']));
        $this->analyse([__DIR__ . '/data/language-getters.php'], [
            ["Language 'de_DE' is not configured in general.valid_languages.", 10],
            ["Language 'DE' is not configured in general.valid_languages.", 11],
            ["Language 'de-DE' is not configured in general.valid_languages.", 12],
        ]);
    }

    public function testInstalledGeneratedLanguagesWithAuthoritativeTarget(): void
    {
        // Keep the generated parameter shape; opt into an authoritative read target on this synthetic copy.
        $xml = simplexml_load_file(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');
        self::assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://symfony.com/schema/dic/services');
        $target = $xml->xpath('//s:parameter[@key="config_location"]/s:parameter[@key="system_settings"]/s:parameter[@key="read_target"]/s:parameter[@key="type"]');
        self::assertIsArray($target);
        self::assertCount(1, $target);
        $target[0][0] = 'symfony-config';
        $contents = $xml->asXML();
        self::assertIsString($contents);
        $this->reader = new ContainerXmlReader(self::writeXml($contents));
        $this->analyse([__DIR__ . '/data/language-getters.php'], [
            ["Language 'de_DE' is not configured in general.valid_languages.", 10],
            ["Language 'DE' is not configured in general.valid_languages.", 11],
            ["Language 'de-DE' is not configured in general.valid_languages.", 12],
        ]);
    }

    public function testDiagnosticIdentifier(): void
    {
        self::assertSame(['pimcore.invalidLanguage', 'pimcore.invalidLanguage'], array_map(
            static fn (Error $error): ?string => $error->getIdentifier(),
            $this->gatherAnalyserErrors([__DIR__ . '/data/language-getters.php']),
        ));
    }

    private static function writeXml(string $xml): string
    {
        $path = sys_get_temp_dir() . '/phpstan-pimcore-language-' . bin2hex(random_bytes(8)) . '.xml';
        file_put_contents($path, $xml);

        return $path;
    }

    protected function getRule(): Rule
    {
        return new LanguageArgumentRule($this->reader ?? new ContainerXmlReader(__DIR__ . '/../fixtures/container/languages.xml'));
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
