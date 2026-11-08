<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\SelectOptionRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<SelectOptionRule> */
final class SelectOptionRuleTest extends RuleTestCase
{
    public function testWrites(): void
    {
        $this->analyse([__DIR__ . '/data/select-options-basic.php'], [
            ["Value 'blue' is not an option of select field Product::\$color.", 11, "Options: 'red', 'green'."],
        ]);
    }

    public function testCallFormsAndValues(): void
    {
        $this->analyse([__DIR__ . '/data/select-options.php'], [
            ["Value 'blue' is not an option of select field Product::\$color.", 15, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 24, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 25, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 26, "Options: 'red', 'green'."],
            ["Value 'autumn' is not an option of select field Product::\$season.", 27, "Options: 'summer', 'winter'."],
            ["Value 'autumn' is not an option of select field Product::\$season.", 28, "Options: 'summer', 'winter'."],
            ["Value 'blue' is not an option of multiselect field Product::\$colors.", 29, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of multiselect field Product::\$colors.", 30, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 31, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of multiselect field Product::\$colors.", 32, "Options: 'red', 'green'."],
            ["Value 'reed' is not an option of select field Product::\$color.", 33, "Options: 'red', 'green'. Did you mean 'red'?"],
            ["Value 'blue' is not an option of select field Product::\$color.", 41, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 42, "Options: 'red', 'green'."],
            ["Value 'inch' is not an option of select field Dimensions::\$unit.", 51, "Options: 'mm', 'cm'."],
            ["Value 'inch' is not an option of select field Dimensions::\$unit.", 52, "Options: 'mm', 'cm'."],
            ["Value 'missing' is not an option of select field StockEntry::\$warehouse.", 53, "Options: 'main'."],
            ["Value 'missing' is not an option of select field StockEntry::\$warehouse.", 54, "Options: 'main'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 55, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 56, "Options: 'red', 'green'."],
            ["Value '2' is not an option of select field Category::\$code.", 62, "Options: '01'. Did you mean '01'?"],
            ["Value 'apricott' is not an option of select field Category::\$name.", 63, "Options: 'apple', 'banana', 'cherry', 'date', 'elderberry', 'fig', 'grape', 'honeydew', 'kiwi', 'lemon', …. Did you mean 'apricot'?"],
        ]);
    }

    public function testValuesArrays(): void
    {
        $this->analyse([__DIR__ . '/data/select-options-values.php'], [
            ["Value 'blue' is not an option of select field Product::\$color.", 17, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of multiselect field Product::\$colors.", 18, "Options: 'red', 'green'."],
            ["Value 'autumn' is not an option of select field Product::\$season.", 19, "Options: 'summer', 'winter'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 20, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 21, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 22, "Options: 'red', 'green'."],
            ["Value 'blue' is not an option of select field Product::\$color.", 23, "Options: 'red', 'green'."],
            ["Value 'inch' is not an option of select field Dimensions::\$unit.", 24, "Options: 'mm', 'cm'."],
            ["Value 'missing' is not an option of select field StockEntry::\$warehouse.", 25, "Options: 'main'."],
        ]);
    }

    public function testIdentifier(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/select-options-basic.php']);
        self::assertSame(['pimcore.selectOption'], array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors));
    }

    public function testSilentWithoutDefinitions(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', sprintf(
            "includes:\n    - '%s/extension.neon'\n    - '%s/rules.neon'\nparameters:\n    level: 0\n    bootstrapFiles:\n        - '%s/tests/bootstrap.php'\n    paths:\n        - '%s/tests/Rules/data/select-options-basic.php'\n        - '%s/tests/Rules/data/select-options-values.php'\n",
            Phpstan::root(),
            Phpstan::root(),
            Phpstan::root(),
            Phpstan::root(),
            Phpstan::root(),
        ));

        self::assertSame([], Phpstan::analyse($project));
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(SelectOptionRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $directory = sys_get_temp_dir() . '/phpstan-pimcore-tests/select-options-' . Fixtures::line();
        foreach (['', '/objectbricks', '/fieldcollections'] as $relative) {
            if (!is_dir($directory . $relative)) {
                mkdir($directory . $relative, 0o777, true);
            }
            foreach (glob(Fixtures::definitionsDirectory() . $relative . '/*.php') ?: [] as $file) {
                copy($file, $directory . $relative . '/' . basename($file));
            }
        }

        // Alternate exported definitions exercise edge configurations without altering the generated fixtures.
        self::replaceField($directory . '/definition_Product.php', 'sku', 'Country', ['blue']);
        self::replaceField($directory . '/definition_Category.php', 'code', 'Select', ['01']);
        self::replaceField($directory . '/definition_Category.php', 'name', 'Select', ['apple', 'banana', 'cherry', 'date', 'elderberry', 'fig', 'grape', 'honeydew', 'kiwi', 'lemon', 'apricot']);
        self::replaceField($directory . '/fieldcollections/StockEntry.php', 'warehouse', 'Select', ['main']);
        $config = $directory . '.neon';
        file_put_contents($config, sprintf("parameters:\n    pimcore:\n        classDefinitionDirectory: '%s'\n", $directory));

        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', $config];
    }

    /** @param list<string> $options */
    private static function replaceField(string $file, string $name, string $type, array $options): void
    {
        $definition = file_get_contents($file);
        self::assertIsString($definition);
        $replacement = sprintf(
            "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\%s::__set_state(array('name' => '%s', 'options' => %s, 'enforceValidation' => true,",
            $type,
            $name,
            var_export(array_map(static fn (string $value): array => ['value' => $value], $options), true),
        );
        $definition = preg_replace_callback(
            '~' . preg_quote('\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Input::__set_state(array(', '~') . "\\s*'name' => '" . $name . "',~",
            static fn (): string => $replacement,
            $definition,
        );
        self::assertIsString($definition);
        file_put_contents($file, $definition);
    }
}
