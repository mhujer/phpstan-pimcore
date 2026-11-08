<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\InterpolatedConditionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<InterpolatedConditionRule>
 */
final class InterpolatedConditionRuleTest extends RuleTestCase
{
    private const string LISTING = 'Pimcore\Model\DataObject\Product\Listing';

    public function testUnsafePartsAreReported(): void
    {
        $this->analyse([__DIR__ . '/data/interpolated-condition-parts.php'], [
            self::condition('setCondition', '$name', 'string', 14),
            self::condition('setCondition', '$name', 'string', 15),
            self::condition('setCondition', '$id', 'int|string', 16),
            self::condition('setCondition', '$value', 'mixed', 17),
            self::condition('setCondition', '$list->escapeLike($name)', 'string', 18),
            self::condition('setCondition', '$field', 'string', 19),
        ]);
    }

    /**
     * Numeric `sprintf()` specifiers are safe whatever the argument; `implode()` is as safe as the array's values.
     */
    public function testSprintfAndImplodeAreCheckedByPart(): void
    {
        $this->analyse([__DIR__ . '/data/interpolated-condition-functions.php'], [
            self::condition('setCondition', '$name', 'string', 29),
            self::condition('setCondition', '$name', 'string', 30),
            self::condition('setCondition', '$format', 'string', 31),
            self::condition('setCondition', '$names', 'list<string>', 32),
            self::condition('setCondition', '$names', 'list<string>', 33),
            self::condition('setCondition', '$name', 'string', 34),
            self::condition('setCondition', '$names', 'list<string>', 35),
        ]);
    }

    /**
     * `setOrderKey()` and `setGroupBy()` are checked only when `$quote` may be false. A bare variable is never checked.
     */
    public function testListingMethodsAreChecked(): void
    {
        $this->analyse([__DIR__ . '/data/interpolated-condition-methods.php'], [
            self::condition('addConditionParam', '$name', 'string', 14),
            self::identifier('setOrderKey', '$name', 15),
            self::identifier('setGroupBy', '$name', 16),
            self::identifier('setOrderKey', '$name', 17),
            self::identifier('setGroupBy', '$name', 18),
            self::condition('setCondition', '$name', 'string', 19),
            self::condition('setCondition', '$name', 'string', 20),
            self::condition('setCondition', '$name', 'string', 21),
            self::condition('setCondition', '$name', 'string', 41, 'Pimcore\Model\DataObject\Listing'),
            self::condition('setCondition', '$name', 'string', 42, 'Pimcore\Model\Asset\Listing'),
            self::condition('setCondition', '$name', 'string', 44),
        ]);
    }

    public function testLiteralListingConfig(): void
    {
        $this->analyse([__DIR__ . '/data/interpolated-condition-config.php'], [
            self::config('getList', 18),
            self::config('getList', 19),
            self::config('getList', 20),
            self::config('getList', 21),
            self::config('getList', 22),
            self::config('setValues', 23),
            self::config('setValue', 24),
            self::config('setValue', 25),
            self::config('getList', 26),
            self::config('setValues', 27),
            self::config('setValues', 28, '[$name]', 'array{string}'),
        ]);
    }

    /**
     * The rule comes from the container, so these tests also cover its registration in rules.neon.
     */
    public function testConfigOverridesAreSkipped(): void
    {
        $this->analyse([__DIR__ . '/data/config-values-overrides.php'], []);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(InterpolatedConditionRule::class);
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }

    /**
     * @return array{string, int, string}
     */
    private static function condition(string $method, string $part, string $type, int $line, string $listing = self::LISTING): array
    {
        $tip = $method === 'addConditionParam'
            ? "Bind the value as a parameter instead, e.g. addConditionParam('name = ?', \$name)."
            : "Bind the value as a parameter instead, e.g. setCondition('name = ?', [\$name]).";

        return [self::message($listing, $method, $part, $type), $line, $tip];
    }

    /**
     * @return array{string, int, string}
     */
    private static function identifier(string $method, string $part, int $line): array
    {
        return [
            self::message(self::LISTING, $method, $part, 'string'),
            $line,
            'Pass a constant identifier and leave $quote as true, so that Pimcore quotes it.',
        ];
    }

    /**
     * @return array{string, int, string}
     */
    private static function config(string $method, int $line, string $part = '$name', string $type = 'string'): array
    {
        return [
            sprintf("%s() option 'condition' embeds %s of type %s, which may allow SQL injection.", $method, $part, $type),
            $line,
            "A Listing config can't bind parameters: call setCondition('name = ?', [\$name]) on the listing instead, or quote the value with Db::get()->quote().",
        ];
    }

    private static function message(string $listing, string $method, string $part, string $type): string
    {
        return sprintf('Argument #1 of %s::%s() embeds %s of type %s, which may allow SQL injection.', $listing, $method, $part, $type);
    }
}
