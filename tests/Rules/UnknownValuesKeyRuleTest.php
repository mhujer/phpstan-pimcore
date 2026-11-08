<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\UnknownValuesKeyRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<UnknownValuesKeyRule> */
final class UnknownValuesKeyRuleTest extends RuleTestCase
{
    public function testCreate(): void
    {
        $this->analyse([__DIR__ . '/data/values-create.php'], [
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 'skuu', but Pimcore\\Model\\DataObject\\Product has no setSkuu(); Pimcore ignores it.", 9, "Did you mean 'sku'?"],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 'value', which Pimcore always ignores.", 11],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 'o_key', which triggers Pimcore's legacy compatibility branch and throws BadMethodCallException.", 12],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with integer key 7; Pimcore ignores it.", 13],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 'completelyUnknown', but Pimcore\\Model\\DataObject\\Product has no setCompletelyUnknown(); Pimcore ignores it.", 14],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 'skuu', but Pimcore\\Model\\DataObject\\Product has no setSkuu(); Pimcore ignores it.", 19, "Did you mean 'sku'?"],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with key 's', but Pimcore\\Model\\DataObject\\Product has no setS(); Pimcore ignores it.", 26],
            ["Call to Pimcore\\Model\\DataObject\\Product::create() with integer key 0; Pimcore ignores it.", 33],
        ]);
    }

    public function testTargets(): void
    {
        $this->analyse([__DIR__ . '/data/values-targets.php'], [
            ["Call to Pimcore\\Model\\Asset::create() with key 'completelyUnknown', but Pimcore\\Model\\Asset has no setCompletelyUnknown(); Pimcore ignores it.", 13],
            ["Call to Pimcore\\Model\\Document::create() with key 'title', but Pimcore\\Model\\Document has no setTitle(); Pimcore ignores it.", 14],
            ["Call to Pimcore\\Model\\DataObject\\Folder::create() with key 'completelyUnknown', but Pimcore\\Model\\DataObject\\Folder has no setCompletelyUnknown(); Pimcore ignores it.", 17],
            ["getList() option 'limitt' has no setter on Pimcore\\Model\\DataObject\\Listing; Pimcore ignores it.", 18, "Did you mean 'limit'?"],
            ["getList() option 'limitt' has no setter on Pimcore\\Model\\Asset\\Listing; Pimcore ignores it.", 19, "Did you mean 'limit'?"],
            ["getList() option 'limitt' has no setter on Pimcore\\Model\\Document\\Listing; Pimcore ignores it.", 20, "Did you mean 'limit'?"],
            ["getList() option 'limitt' has no setter on Pimcore\\Model\\DataObject\\Product\\Listing; Pimcore ignores it.", 21, "Did you mean 'limit'?"],
            ["getList() option 'filterBySku' has no setter on Pimcore\\Model\\DataObject\\Listing; Pimcore ignores it.", 22],
            ["getList() option 'filterBySku' has no setter on Pimcore\\Model\\DataObject\\Product\\Listing; Pimcore ignores it.", 23],
            ["getList() option 'objectTypes' has no setter on Pimcore\\Model\\Asset\\Listing; Pimcore ignores it.", 29],
        ]);
    }

    public function testInstances(): void
    {
        require_once __DIR__ . '/data/values-instance.php';

        $this->analyse([__DIR__ . '/data/values-instance.php'], [
            ["Call to Pimcore\\Model\\DataObject\\Product::setValues() with key 'completelyUnknown', but Pimcore\\Model\\DataObject\\Product has no setCompletelyUnknown(); Pimcore ignores it.", 15],
            ["Call to Pimcore\\Model\\DataObject\\Product::setValue() with key 'completelyUnknown', but Pimcore\\Model\\DataObject\\Product has no setCompletelyUnknown(); Pimcore ignores it.", 16],
            ["Call to Pimcore\\Model\\DataObject\\Product\\Listing::setValues() with key 'limitt', but Pimcore\\Model\\DataObject\\Product\\Listing has no setLimitt(); Pimcore ignores it.", 17, "Did you mean 'limit'?"],
            ["Call to Pimcore\\Model\\DataObject\\Product\\Listing::setValue() with key 'limitt', but Pimcore\\Model\\DataObject\\Product\\Listing has no setLimitt(); Pimcore ignores it.", 18, "Did you mean 'limit'?"],
            ["Call to ValuesInstance\\ExactDocument::setValues() with key 'completelyUnknown', but ValuesInstance\\ExactDocument has no setCompletelyUnknown(); Pimcore ignores it.", 34],
            ["Call to ValuesInstance\\ExactDocument::setValue() with key 'ghost', but ValuesInstance\\ExactDocument has no setGhost(); Pimcore ignores it.", 35],
        ]);
    }

    public function testIdentifier(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/values-create.php']);
        self::assertSame(['pimcore.unknownValuesKey'], array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors))));
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(UnknownValuesKeyRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
