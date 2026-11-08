<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\UnknownFieldNameRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnknownFieldNameRule>
 */
final class UnknownFieldNameRuleTest extends RuleTestCase
{
    public function testGetMatchesTheGetterCaseInsensitively(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-get.php'], [
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'nosuch'), 15],
        ]);
    }

    public function testGetValueForFieldNameMatchesThePropertyWithTheExactCase(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-value-for-field-name.php'], [
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'SKU'), 14],
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'nosuch'), 15],
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'Sku'), 16],
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'Name'), 17],
            [
                "Call to Pimcore\\Model\\DataObject\\Product::getValueForFieldName() with localized field 'title' always returns null.",
                22,
                "Use get('title', \$language) or getTitle(\$language) instead.",
            ],
        ]);
    }

    public function testOnlyGeneratedClassesAreChecked(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-receivers.php'], [
            [self::unknown('Pimcore\Model\DataObject\Product\Attributes', 'get', 'nosuch'), 38],
            [self::unknown('Pimcore\Model\DataObject\Objectbrick\Data\Dimensions', 'get', 'nosuch'), 41],
            [self::unknown('Pimcore\Model\DataObject\Objectbrick\Data\Dimensions', 'getValueForFieldName', 'nosuch'), 42],
            [self::unknown('Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier', 'get', 'nosuch'), 44],
            [self::unknown('Pimcore\Model\DataObject\Category|Pimcore\Model\DataObject\Product', 'get', 'sku'), 50],
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'nosuch'), 51],
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'nosuch'), 52],
        ]);
    }

    public function testEveryConstantFieldNameIsChecked(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-names.php'], [
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'nosuch'), 18],
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'other'), 18],
            [
                "Call to Pimcore\\Model\\DataObject\\Product::getValueForFieldName() with localized field 'title' always returns null.",
                19,
                "Use get('title', \$language) or getTitle(\$language) instead.",
            ],
            [self::unknown('Pimcore\Model\DataObject\Product', 'get', 'nosuch'), 25],
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'nosuch'), 26],
        ]);
    }

    /**
     * A getter of the generated class itself without a property is a calculated value, on a class override, too. A
     * Pimcore getter such as `getFullPath()` doesn't make `fullPath` a field.
     */
    public function testGetValueForFieldNameAcceptsCalculatedValues(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-calculated-value.php'], [
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'fullPath'), 14],
            [self::unknown('Pimcore\Model\DataObject\Product', 'getValueForFieldName', 'Margin'), 15],
            [self::unknown('Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverriddenProduct', 'getValueForFieldName', 'Margin'), 21],
        ]);
    }

    public function testSetChecksSettersAndOnlyImpossibleValues(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-set.php'], [
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'set', 'nosuch'), 19],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'set', 'margin'), 20],
            ["Parameter #2 \$value of Pimcore\\Model\\DataObject\\Product::set('sku') expects string|null, int given.", 21],
        ]);
        self::assertSame(
            ['pimcore.unknownFieldName', 'pimcore.unknownFieldName', 'pimcore.fieldValueType'],
            array_map(static fn (Error $error): ?string => $error->getIdentifier(), $this->gatherAnalyserErrors([__DIR__ . '/data/unknown-field-set.php'])),
        );
    }

    public function testLocalizedNamesUseExactCaseAndParentNamesUseGetters(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-localized.php'], [
            [self::unknown('Pimcore\\Model\\DataObject\\Localizedfield<Pimcore\\Model\\DataObject\\Product>', 'getLocalizedValue', 'Title'), 20],
            [self::unknown('Pimcore\\Model\\DataObject\\Localizedfield<Pimcore\\Model\\DataObject\\Product>', 'setLocalizedValue', 'sku'), 21],
            [self::unknown('Pimcore\\Model\\DataObject\\Localizedfield<Pimcore\\Model\\DataObject\\Product>', 'getLocalizedValue', 'nosuch'), 22],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'getValueFromParent', 'nosuch'), 28],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'getByLocalizedfields', 'Title'), 32],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'getByLocalizedfields', 'nosuch'), 33],
        ]);
    }

    public function testSetAndParentCheckEveryNameOnGeneratedReceivers(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-field-set-receivers.php'], [
            [self::unknown('Pimcore\\Model\\DataObject\\Product\\Attributes', 'set', 'nosuch'), 17],
            [self::unknown('Pimcore\\Model\\DataObject\\Objectbrick\\Data\\Dimensions', 'set', 'nosuch'), 19],
            [self::unknown('Pimcore\\Model\\DataObject\\Fieldcollection\\Data\\PriceTier', 'set', 'nosuch'), 21],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'set', 'nosuch'), 22],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'set', 'nosuch'), 25],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'set', 'other'), 25],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'getValueFromParent', 'nosuch'), 26],
            [self::unknown('Pimcore\\Model\\DataObject\\Product', 'getValueFromParent', 'other'), 26],
        ]);
    }

    /**
     * The rule comes from the container, so these tests also cover its registration in rules.neon.
     */
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(UnknownFieldNameRule::class);
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }

    private static function unknown(string $class, string $method, string $fieldName): string
    {
        return sprintf("Call to %s::%s() with unknown field name '%s'.", $class, $method, $fieldName);
    }
}
