<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ConditionPlaceholdersRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ConditionPlaceholdersRule> */
final class ConditionPlaceholdersRuleTest extends RuleTestCase
{
    public function testPositionalBindings(): void
    {
        $prefix = 'Argument #1 of Pimcore\\Model\\DataObject\\Product\\Listing::';
        $spread = ' has 1 placeholder, but the array binds each element as its own parameter.';
        $tip = 'Wrap the array to bind it as one parameter: [$ids].';
        $this->analyse([__DIR__ . '/data/condition-placeholders.php'], [
            [$prefix . 'setCondition() has 2 placeholders, but 1 parameter is bound.', 9],
            [$prefix . 'setCondition()' . $spread, 10, $tip],
            [$prefix . 'addConditionParam() has 2 placeholders, but 1 parameter is bound.', 11],
            [$prefix . 'addConditionParam()' . $spread, 12, $tip],
            [$prefix . 'setCondition() has 1 placeholder, but 0 parameters are bound.', 13],
            [$prefix . 'setCondition() has 1 placeholder, but 0 parameters are bound.', 14],
            [$prefix . 'setCondition() has 0 placeholders, but 1 parameter is bound.', 15],
        ]);
    }

    public function testNamedAndMixedBindings(): void
    {
        $prefix = 'Argument #1 of Pimcore\\Model\\DataObject\\Product\\Listing::';
        $this->analyse([__DIR__ . '/data/condition-placeholders-named.php'], [
            [$prefix . 'setCondition() uses :name, but no "name" parameter is bound.', 9],
            [$prefix . 'addConditionParam() binds "foo", which the condition doesn\'t use.', 10],
            [$prefix . 'setCondition() mixes ? and :name placeholders, which PDO doesn\'t allow.', 13],
            [$prefix . 'addConditionParam() mixes ? and :name placeholders, which PDO doesn\'t allow.', 14],
            [$prefix . 'setCondition() uses :name, but no "name" parameter is bound.', 15],
            [$prefix . 'setCondition() has 1 placeholder, but 0 parameters are bound.', 16],
            [$prefix . 'addConditionParam() uses :name, but no "name" parameter is bound.', 17],
            [$prefix . 'setCondition() has 0 placeholders, but 1 parameter is bound.', 18],
        ]);
    }

    public function testDynamicInputsAreConservative(): void
    {
        $prefix = 'Argument #1 of Pimcore\\Model\\DataObject\\Product\\Listing::';
        $spread = ' has 1 placeholder, but the array binds each element as its own parameter.';
        $tip = 'Wrap the array to bind it as one parameter: [$ids].';
        $this->analyse([__DIR__ . '/data/condition-placeholders-dynamic.php'], [
            [$prefix . 'setCondition()' . $spread, 16, $tip],
            [$prefix . 'addConditionParam()' . $spread, 17, $tip],
            [$prefix . 'setCondition() has 3 placeholders, but 1 parameter is bound.', 22],
            [$prefix . 'setCondition() has 2 placeholders, but 1 parameter is bound.', 23],
            [$prefix . 'addConditionParam() has 2 placeholders, but 1 parameter is bound.', 24],
            [$prefix . 'setCondition() has 2 placeholders, but 3 parameters are bound.', 29],
            [$prefix . 'addConditionParam() has 2 placeholders, but 3 parameters are bound.', 30],
            [$prefix . 'setCondition() has 2 placeholders, but 1 parameter is bound.', 31],
            [$prefix . 'setCondition() has 1 placeholder, but 2 parameters are bound.', 34],
            [$prefix . 'addConditionParam() has 1 placeholder, but 2 parameters are bound.', 35],
            [$prefix . 'addConditionParam() has 2 placeholders, but 1 parameter is bound.', 37],
            [$prefix . 'setCondition() has 2 placeholders, but 1 parameter is bound.', 38],
            [$prefix . 'setCondition()' . $spread, 39, $tip],
        ]);
    }

    public function testSqlTokensAndPimcoreValueIgnoring(): void
    {
        $prefix = 'Argument #1 of Pimcore\\Model\\DataObject\\Product\\Listing::addConditionParam()';
        $this->analyse([__DIR__ . '/data/condition-placeholders-quotes.php'], [
            [$prefix . ' has 0 placeholders, but 1 parameter is bound.', 15],
            [$prefix . ' has 0 placeholders, but 1 parameter is bound.', 16],
            [$prefix . ' has 0 placeholders, but 1 parameter is bound.', 17],
            [$prefix . ' has 0 placeholders, but 1 parameter is bound.', 18],
        ]);
    }

    public function testFilterByPlaceholderCounts(): void
    {
        $prefix = 'Argument #2 of Pimcore\\Model\\DataObject\\Product\\Listing::';
        $this->analyse([__DIR__ . '/data/condition-placeholders-filters.php'], [
            [$prefix . 'filterBySku() has 2 placeholders, but 1 parameter is bound.', 9],
            [$prefix . 'filterById() has 2 placeholders, but 1 parameter is bound.', 10],
            [$prefix . 'filterByKey() has 1 placeholder, but the array binds each element as its own parameter.', 11, 'Wrap the array to bind it as one parameter: [$ids].'],
            [$prefix . 'filterBySku() has 1 placeholder, but 0 parameters are bound.', 12],
        ]);
    }

    public function testFilterInputs(): void
    {
        require_once __DIR__ . '/data/condition-placeholders-filter-classes.php';

        $prefix = 'Argument #2 of Pimcore\\Model\\DataObject\\Product\\Listing::';
        $this->analyse([__DIR__ . '/data/condition-placeholders-filter-inputs.php'], [
            [$prefix . 'filterBySku() has 2 placeholders, but 1 parameter is bound.', 15],
            [$prefix . 'filterByKey() has 1 placeholder, but the array binds each element as its own parameter.', 16, 'Wrap the array to bind it as one parameter: [$ids].'],
            [$prefix . 'filterBySku() has 0 placeholders, but 1 parameter is bound.', 17],
            [$prefix . 'filterBySku() has 0 placeholders, but 1 parameter is bound.', 21],
            [$prefix . "filterBySku() mixes ? and :name placeholders, which PDO doesn't allow.", 22],
            ['Pimcore\\Model\\DataObject\\Product\\Listing::filterByMainCategory() only supports the "=" operator for a many-to-one relation.', 24],
            ['Argument #2 of InheritedFilters::filterBySku() has 2 placeholders, but 1 parameter is bound.', 27],
        ]);
    }

    public function testManyToOneRelationOperators(): void
    {
        $this->analyse([__DIR__ . '/data/condition-placeholders-relations.php'], [
            ['Pimcore\\Model\\DataObject\\Product\\Listing::filterByMainCategory() only supports the "=" operator for a many-to-one relation.', 11],
            ['Pimcore\\Model\\DataObject\\Product\\Listing::filterByMainCategory() only supports the "=" operator for a many-to-one relation.', 12],
            ['Argument #2 of Pimcore\\Model\\DataObject\\Product\\Listing::filterByCategories() has 1 placeholder, but the array binds each element as its own parameter.', 17, 'Wrap the array to bind it as one parameter: [$ids].'],
        ]);
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/condition-placeholders-relations.php']);
        self::assertSame(['pimcore.relationFilterOperator', 'pimcore.relationFilterOperator', 'pimcore.conditionPlaceholders'], array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors));
    }

    public function testFiltersWithoutDefinitions(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', sprintf(
            "includes:\n    - '%s/extension.neon'\n    - '%s/rules.neon'\nparameters:\n    level: 0\n    bootstrapFiles:\n        - '%s/tests/bootstrap.php'\n    paths:\n        - '%s/tests/Rules/data/condition-placeholders-relations.php'\n",
            Phpstan::root(),
            Phpstan::root(),
            Phpstan::root(),
            Phpstan::root(),
        ));

        self::assertSame([], Phpstan::analyse($project));
    }

    public function testIdentifier(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/condition-placeholders.php']);
        self::assertSame(['pimcore.conditionPlaceholders'], array_values(array_unique(
            array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors),
        )));
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ConditionPlaceholdersRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        $config = sys_get_temp_dir() . '/phpstan-pimcore-condition-placeholders-' . Fixtures::line() . '.neon';
        file_put_contents($config, sprintf("parameters:\n    pimcore:\n        classDefinitionDirectory: '%s'\n", Fixtures::definitionsDirectory()));

        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', $config];
    }
}
