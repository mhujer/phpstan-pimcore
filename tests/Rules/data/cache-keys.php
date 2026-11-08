<?php

declare(strict_types=1);

namespace CacheKeys;

use Pimcore\Cache;
use Pimcore\Cache\Core\CoreCacheHandler;

Cache::load('product:1');
Cache::save(null, 'menu/main', ['nav:main']);
Cache::remove('a@b');
Cache::clearTag('x{y}');
Cache::clearTags(['ok', 'b\\c']);
Cache::addClearTagOnShutdown('t:');
Cache::addIgnoredTagOnSave('t:');
Cache::removeIgnoredTagOnSave('t:');
Cache::addIgnoredTagOnClear('t:');
Cache::removeIgnoredTagOnClear('t:');
Cache::getHandler()->save('k:', null, ['tag:']);
Cache::getHandler()->getItem('k:');
Cache::save(null, '');
Cache::remove('');
Cache::clearTags(['']);

function handler(CoreCacheHandler $handler): void
{
    $handler->load('k:');
    $handler->remove('k:');
    $handler->clearTag('t:');
    $handler->clearTags(['t:']);
    $handler->addTagClearedOnShutdown('t:');
    $handler->addTagIgnoredOnSave('t:');
    $handler->removeTagIgnoredOnSave('t:');
    $handler->addTagIgnoredOnClear('t:');
    $handler->removeTagIgnoredOnClear('t:');
    $handler->save(tags: ['t:'], data: null, key: 'k:');
}
