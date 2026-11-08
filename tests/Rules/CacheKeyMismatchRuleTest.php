<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\CacheKeyCallCollector;
use Mhujer\PHPStan\Pimcore\Rules\CacheKeyMismatchRule;
use Mhujer\PHPStan\Pimcore\Rules\CacheKeyPairCollector;
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<CacheKeyMismatchRule> */
final class CacheKeyMismatchRuleTest extends RuleTestCase
{
    public function testDifferentConstantKeys(): void
    {
        $this->analyse([__DIR__ . '/data/cache-key-mismatch.php'], [
            ['Cache save key differs from the key used to load $data in the same function.', 13],
        ]);
    }

    public function testConcatenationContainsTheOtherWholeKey(): void
    {
        $this->analyse([__DIR__ . '/data/cache-key-concatenation.php.fixture'], [
            ['Cache save key differs from the key used to load $data in the same function.', 18],
            ['Cache save key differs from the key used to load $data in the same function.', 25],
        ]);
    }

    public function testAmbiguousAndUnrelatedFlowsAreSkipped(): void
    {
        $this->analyse([__DIR__ . '/data/cache-key-ambiguous.php.fixture'], []);
    }

    public function testIdentifierAndDefaultRegistration(): void
    {
        $rules = self::getContainer()->getServicesByTag('phpstan.rules.rule');
        self::assertCount(1, array_filter($rules, static fn (mixed $rule): bool => $rule instanceof CacheKeyMismatchRule));
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/cache-key-mismatch.php']);
        self::assertSame(['pimcore.cacheKeyMismatch'], array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors));
    }

    /** @return list<Collector<Node, mixed>> */
    protected function getCollectors(): array
    {
        return [self::getContainer()->getByType(CacheKeyCallCollector::class), self::getContainer()->getByType(CacheKeyPairCollector::class)];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CacheKeyMismatchRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
