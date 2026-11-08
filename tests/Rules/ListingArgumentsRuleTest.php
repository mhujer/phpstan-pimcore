<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ListingArgumentsRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ListingArgumentsRule> */
final class ListingArgumentsRuleTest extends RuleTestCase
{
    public function testOrder(): void
    {
        $prefix = 'Call to Pimcore\\Model\\DataObject\\Product\\Listing::setOrder() with invalid order ';
        $suffix = '; Pimcore accepts only ASC or DESC.';
        $this->analyse([__DIR__ . '/data/listing-order.php'], [
            [$prefix . "'random'" . $suffix, 10],
            [$prefix . "' asc'" . $suffix, 11],
            [$prefix . "'foo'" . $suffix, 12],
            [$prefix . "'bad'" . $suffix, 14],
            ["Call to Pimcore\\Model\\DataObject\\Product::getList() option 'order' with invalid order 'random'" . $suffix, 16],
            ["Call to Pimcore\\Model\\DataObject\\Product\\Listing::setValues() option 'order' with invalid order 'random'" . $suffix, 17],
        ]);
    }

    public function testOrderKey(): void
    {
        $prefix = 'Call to Pimcore\\Model\\DataObject\\Product\\Listing::';
        $suffix = ', which Pimcore quotes as a single column name.';
        $tip = 'Pass false as the second argument to use it unquoted.';
        $this->analyse([__DIR__ . '/data/listing-key.php'], [
            [$prefix . "setOrderKey() with expression 'RAND()'" . $suffix, 9, $tip],
            [$prefix . "setOrderKey() with expression 'RAND()'" . $suffix, 10, $tip],
            [$prefix . "setOrderKey() with expression 'name DESC'" . $suffix, 12, $tip],
            [$prefix . "setOrderKey() with expression '`name`'" . $suffix, 15, $tip],
            [$prefix . "setGroupBy() with expression 'DATE(created)'" . $suffix, 17, $tip],
            ["Call to Pimcore\\Model\\DataObject\\Product::getList() option 'orderKey' with expression 'RAND()'" . $suffix, 21, 'Call setOrderKey(…, false) on the listing to use it unquoted.'],
            [$prefix . "setValues() option 'groupBy' with expression 'DATE(created)'" . $suffix, 22, 'Call setGroupBy(…, false) on the listing to use it unquoted.'],
        ]);
    }

    public function testIgnoredConditionVariables(): void
    {
        $tip = 'Pass the values to setCondition() or addConditionParam().';
        $suffix = ' has no effect; loading the listing overwrites them.';
        $this->analyse([__DIR__ . '/data/listing-variables.php'], [
            ['Call to Pimcore\\Model\\DataObject\\Product\\Listing::setConditionVariables()' . $suffix, 12, $tip],
            ["Call to Pimcore\\Model\\DataObject\\Product::getList() option 'conditionVariables'" . $suffix, 13, $tip],
            ["Call to Pimcore\\Model\\DataObject\\Product\\Listing::setValues() option 'conditionVariables'" . $suffix, 14, $tip],
            ["Call to Pimcore\\Model\\Asset::getList() option 'conditionVariables'" . $suffix, 15, $tip],
            ["Call to Pimcore\\Model\\Document::getList() option 'conditionVariables'" . $suffix, 16, $tip],
            ["Call to Pimcore\\Model\\DataObject::getList() option 'conditionVariables'" . $suffix, 17, $tip],
        ]);
    }

    public function testObjectTypes(): void
    {
        $prefix = 'Call to Pimcore\\Model\\DataObject\\Product';
        $bad = " with unknown object type 'variants'.";
        $this->analyse([__DIR__ . '/data/listing-object-types.php'], [
            [$prefix . '\\Listing::setObjectTypes()' . $bad, 10],
            [$prefix . '::getChildren()' . $bad, 11],
            [$prefix . '::hasChildren()' . $bad, 12],
            [$prefix . '::getSiblings()' . $bad, 13],
            [$prefix . '::hasSiblings()' . $bad, 14],
            [$prefix . "::getBySku() with object type 'folder', which finders don't support.", 17],
            [$prefix . "::getBySku() with unknown object type 'invalid'.", 19],
            [$prefix . "::getList() option 'objectTypes'" . $bad, 20],
            [$prefix . "\\Listing::setValues() option 'objectTypes'" . $bad, 21],
        ]);
    }

    public function testConfigAndAllConstantValues(): void
    {
        $prefix = 'Call to Pimcore\\Model\\DataObject\\Product\\Listing::';
        $suffix = ', which Pimcore quotes as a single column name.';
        $tip = 'Pass false as the second argument to use it unquoted.';
        $this->analyse([__DIR__ . '/data/listing-config.php'], [
            ["Call to Pimcore\\Model\\Asset::getList() option 'ORDER' with invalid order 'bad'; Pimcore accepts only ASC or DESC.", 12],
            ["Call to Pimcore\\Model\\Document::getList() option 'orderKey' with expression 'a, b'" . $suffix, 13, 'Call setOrderKey(…, false) on the listing to use it unquoted.'],
            [$prefix . "setValues() option 'OBJECTTYPES' with unknown object type 'Object'.", 15],
            [$prefix . "setOrder() with invalid order 'bad1'; Pimcore accepts only ASC or DESC.", 16],
            [$prefix . "setOrder() with invalid order 'bad2'; Pimcore accepts only ASC or DESC.", 16],
            [$prefix . "setOrderKey() with expression 'bad key'" . $suffix, 17, $tip],
            [$prefix . "setOrderKey() with expression 'bad(key)'" . $suffix, 17, $tip],
            [$prefix . "setGroupBy() with expression 'DATE(b)'" . $suffix, 18, $tip],
        ]);
    }

    public function testReceiverExclusions(): void
    {
        $this->analyse([__DIR__ . '/data/listing-exclusions.php'], []);
    }

    public function testIdentifiers(): void
    {
        $errors = $this->gatherAnalyserErrors([
            __DIR__ . '/data/listing-order.php',
            __DIR__ . '/data/listing-key.php',
            __DIR__ . '/data/listing-variables.php',
            __DIR__ . '/data/listing-object-types.php',
        ]);
        $identifiers = array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors)));
        sort($identifiers);
        self::assertSame(['pimcore.conditionVariablesIgnored', 'pimcore.listingOrder', 'pimcore.listingOrderKey', 'pimcore.objectType'], $identifiers);
    }

    public function testConfigOverridesAreSkipped(): void
    {
        $this->analyse([__DIR__ . '/data/config-values-overrides.php'], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ListingArgumentsRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
