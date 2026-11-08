<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\CacheKeyRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<CacheKeyRule> */
final class CacheKeyRuleTest extends RuleTestCase
{
    public function testKeys(): void
    {
        $this->analyse([__DIR__ . '/data/cache-keys.php'], [
            ['Cache key "product:1" passed to Pimcore\\Cache::load() contains reserved character ":".', 10, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "menu/main" passed to Pimcore\\Cache::save() contains reserved character "/".', 11, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "nav:main" passed to Pimcore\\Cache::save() contains reserved character ":".', 11, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "a@b" passed to Pimcore\\Cache::remove() contains reserved character "@".', 12, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "x{y}" passed to Pimcore\\Cache::clearTag() contains reserved character "{".', 13, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "b\\c" passed to Pimcore\\Cache::clearTags() contains reserved character "\\".', 14, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::addClearTagOnShutdown() contains reserved character ":".', 15, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::addIgnoredTagOnSave() contains reserved character ":".', 16, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::removeIgnoredTagOnSave() contains reserved character ":".', 17, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::addIgnoredTagOnClear() contains reserved character ":".', 18, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::removeIgnoredTagOnClear() contains reserved character ":".', 19, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "k:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::save() contains reserved character ":".', 20, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "tag:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::save() contains reserved character ":".', 20, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "k:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::getItem() contains reserved character ":".', 21, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key passed to Pimcore\\Cache::save() is empty.', 22],
            ['Cache key passed to Pimcore\\Cache::remove() is empty.', 23],
            ['Cache tag passed to Pimcore\\Cache::clearTags() is empty.', 24],
            ['Cache key "k:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::load() contains reserved character ":".', 28, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "k:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::remove() contains reserved character ":".', 29, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::clearTag() contains reserved character ":".', 30, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::clearTags() contains reserved character ":".', 31, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::addTagClearedOnShutdown() contains reserved character ":".', 32, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::addTagIgnoredOnSave() contains reserved character ":".', 33, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::removeTagIgnoredOnSave() contains reserved character ":".', 34, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::addTagIgnoredOnClear() contains reserved character ":".', 35, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::removeTagIgnoredOnClear() contains reserved character ":".', 36, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "k:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::save() contains reserved character ":".', 37, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache\\Core\\CoreCacheHandler::save() contains reserved character ":".', 37, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
        ]);
    }

    public function testConstantPartsAndExclusions(): void
    {
        $this->analyse([__DIR__ . '/data/cache-parts.php'], [
            ['Cache key "product:" passed to Pimcore\\Cache::load() contains reserved character ":".', 16, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "menu/" passed to Pimcore\\Cache::load() contains reserved character "/".', 17, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "p:" passed to Pimcore\\Cache::load() contains reserved character ":".', 18, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "b:c" passed to Pimcore\\Cache::load() contains reserved character ":".', 19, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "bad:" passed to Pimcore\\Cache::clearTags() contains reserved character ":".', 21, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "tag:" passed to Pimcore\\Cache::save() contains reserved character ":".', 22, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "tag/" passed to Pimcore\\Cache::save() contains reserved character "/".', 22, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache tag "t:" passed to Pimcore\\Cache::save() contains reserved character ":".', 22, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "left:" passed to Pimcore\\Cache::load() contains reserved character ":".', 23, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key "x%:" passed to Pimcore\\Cache::load() contains reserved character ":".', 25, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
            ['Cache key passed to Pimcore\\Cache::load() is empty.', 37],
            ['Cache key passed to Pimcore\\Cache::load() is empty.', 38],
            ['Cache tag "literal:" passed to Pimcore\\Cache::clearTags() contains reserved character ":".', 39, 'Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.'],
        ]);
    }

    public function testIdentifiers(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/cache-keys.php']);
        $identifiers = array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors)));
        sort($identifiers);

        self::assertSame(['pimcore.cacheEmptyKey', 'pimcore.cacheReservedCharacters'], $identifiers);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CacheKeyRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
