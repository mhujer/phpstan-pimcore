<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ToggleCallCollector;
use Mhujer\PHPStan\Pimcore\Rules\ToggleNotRestoredRule;
use Mhujer\PHPStan\Pimcore\Rules\ToggleScopeCollector;
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Classes the rule has to reflect (a trait, an additional toggle) are in Fixtures/, which is autoloaded: PHPStan's
 * test reflection doesn't see classes declared in the analysed data files.
 *
 * @extends RuleTestCase<ToggleNotRestoredRule>
 */
final class ToggleNotRestoredRuleTest extends RuleTestCase
{
    private const string INHERITED_VALUES_TIP = 'Use Pimcore\Model\DataObject\Service::useInheritedValues(), which restores the previous value for you.';

    public function testFlipWithoutRestoreIsReported(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-not-restored.php'], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 11],
        ]);
    }

    public function testRestoreMustBeInAFinallyOfTheSameFunction(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-restored.php'], [
            [self::notInFinally('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 34],
        ]);
    }

    public function testEveryToggleGroupIsMatchedByTheDeclaringClass(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-groups.php'], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 17],
            [self::notRestored('Pimcore\Model\Document::setHideUnpublished()'), 18],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 19, self::INHERITED_VALUES_TIP],
            [self::notRestored('Pimcore\Model\Document\PageSnippet::setGetInheritedValues()'), 20],
            [self::notRestored('Pimcore\Model\Element\AbstractElement::setGetInheritedProperties()'), 21],
            [self::notRestored('Pimcore\Model\DataObject\Localizedfield::setGetFallbackValues()'), 22],
            [self::notRestored('Pimcore\Model\DataObject\Localizedfield::setStrictMode()'), 23],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::disableDirtyDetection()'), 24],
            [self::notRestored('Pimcore\Model\Version::disable()'), 25],
            [self::notRestored('Pimcore\Cache::disable()'), 26],
            [self::notRestored('Pimcore\Cache\RuntimeCache::disable()'), 27],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 32],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 33, self::INHERITED_VALUES_TIP],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::disableDirtyDetection()'), 34],
            [self::notRestored('Pimcore\Model\Version::disable()'), 42],
            [self::notRestored('Pimcore\Cache\RuntimeCache::enable()'), 47],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 73],
        ]);
    }

    public function testKeyAndPathMustBeRestoredInFinally(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-key-and-path.php'], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setDoNotRestoreKeyAndPath()'), 11],
            [self::notInFinally('Pimcore\Model\DataObject\AbstractObject::setDoNotRestoreKeyAndPath()'), 16],
        ]);
    }

    public function testInheritanceRuntimeCacheMustBeRestoredInFinally(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-inheritance-runtime-cache.php'], [
            [self::notRestored('Pimcore\Model\DataObject\Concrete\Dao\InheritanceHelper::setUseRuntimeCache()'), 11],
            [self::notInFinally('Pimcore\Model\DataObject\Concrete\Dao\InheritanceHelper::setUseRuntimeCache()'), 16],
        ]);
    }

    public function testImmediateCacheWritesMustBeRestoredInFinally(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-immediate-cache-writes.php'], [
            [self::notRestored('Pimcore\Cache::setForceImmediateWrite()'), 11],
            [self::notInFinally('Pimcore\Cache::setForceImmediateWrite()'), 16],
            [self::notRestored('Pimcore\Cache::setForceImmediateWrite()'), 31],
            [self::notRestored('Pimcore\Cache::disable()'), 41],
        ]);
    }

    public function testMailDebugModeMustBeRestoredInFinally(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-mail-debug-mode.php'], [
            [self::notRestored('Pimcore\Mail::setForceDebugMode()'), 11],
            [self::notInFinally('Pimcore\Mail::setForceDebugMode()'), 16],
        ]);
    }

    public function testRemoveDynamicOptionsMustBeRestoredInFinally(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-remove-dynamic-options.php'], [
            [self::notRestored('Pimcore\Model\DataObject\ClassDefinition\Service::setDoRemoveDynamicOptions()'), 11],
            [self::notInFinally('Pimcore\Model\DataObject\ClassDefinition\Service::setDoRemoveDynamicOptions()'), 16],
        ]);
    }

    public function testExtendedTogglesReuseBothExistingIdentifiers(): void
    {
        foreach (['key-and-path', 'inheritance-runtime-cache', 'mail-debug-mode', 'remove-dynamic-options'] as $toggle) {
            self::assertSame(
                ['pimcore.toggleNotRestored', 'pimcore.toggleNotRestoredInFinally'],
                array_map(
                    static fn (Error $error): ?string => $error->getIdentifier(),
                    $this->gatherAnalyserErrors([__DIR__ . '/data/toggle-' . $toggle . '.php']),
                ),
            );
        }

        self::assertSame(
            ['pimcore.toggleNotRestored', 'pimcore.toggleNotRestoredInFinally', 'pimcore.toggleNotRestored', 'pimcore.toggleNotRestored'],
            array_map(
                static fn (Error $error): ?string => $error->getIdentifier(),
                $this->gatherAnalyserErrors([__DIR__ . '/data/toggle-immediate-cache-writes.php']),
            ),
        );
    }

    public function testEveryFunctionMethodClosureAndArrowFunctionIsItsOwnScope(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-scopes.php'], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 9],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 14],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 16, self::INHERITED_VALUES_TIP],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 21],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 22, self::INHERITED_VALUES_TIP],
            [self::notInFinally('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 31],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 42],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 49],
        ]);
    }

    public function testATraitIsReportedOnceForAllTheClassesUsingIt(): void
    {
        $this->analyse([
            __DIR__ . '/Fixtures/FlipsInATrait.php',
            __DIR__ . '/Fixtures/FirstTraitUser.php',
            __DIR__ . '/Fixtures/SecondTraitUser.php',
        ], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 13],
        ]);
    }

    public function testObjectInheritedValuesSuggestTheScopedHelper(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-tip.php'], [
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 12, self::INHERITED_VALUES_TIP],
            [self::notInFinally('Pimcore\Model\DataObject\AbstractObject::setGetInheritedValues()'), 17, self::INHERITED_VALUES_TIP],
            [self::notRestored('Pimcore\Model\Document\PageSnippet::setGetInheritedValues()'), 23],
        ]);
    }

    public function testAdditionalTogglesAreAddedToTheBuiltInOnes(): void
    {
        $this->analyse([__DIR__ . '/data/toggle-additional.php'], [
            [self::notRestored('Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\FeatureSwitches::disableIndexing()'), 12],
            [self::notRestored('Pimcore\Model\DataObject\AbstractObject::setHideUnpublished()'), 20],
        ]);
    }

    /**
     * The rule and its collectors come from the container, so these tests also cover their registration in
     * rules.neon.
     */
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ToggleNotRestoredRule::class);
    }

    /**
     * @return list<Collector<Node, mixed>>
     */
    protected function getCollectors(): array
    {
        return [
            self::getContainer()->getByType(ToggleCallCollector::class),
            self::getContainer()->getByType(ToggleScopeCollector::class),
        ];
    }

    /**
     * Includes an `additionalToggles` entry.
     *
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon', __DIR__ . '/toggle-not-restored.neon'];
    }

    private static function notRestored(string $toggle): string
    {
        return sprintf('Toggle %s is flipped and never restored in the same function.', $toggle);
    }

    private static function notInFinally(string $toggle): string
    {
        return sprintf('Toggle %s is restored outside a finally block.', $toggle);
    }
}
