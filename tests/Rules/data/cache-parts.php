<?php

declare(strict_types=1);

namespace CacheParts;

use Pimcore\Cache;
use Pimcore\Cache\RuntimeCache;
use Psr\Cache\CacheItemPoolInterface;

/** @param 'a'|'b:c' $union
 * @param array<string> $tags
 */
function parts(string $id, string $key, array $tags, string $union, CacheItemPoolInterface $pool): void
{
    Cache::load('product:' . $id);
    Cache::load("menu/$id");
    Cache::load(sprintf('p:%d', $id));
    Cache::load($union);
    $constantTags = ['ok', 'bad:'];
    Cache::clearTags($constantTags);
    Cache::save(null, 'ok', ['tag:' . $id, "tag/$id", sprintf('t:%s', $id)]);
    Cache::load('left:' . $id . '/right');
    Cache::load(sprintf('%1$\':10s', $key));
    Cache::load(sprintf('x%%:%s', $key));
    Cache::load('product_' . $id);
    Cache::load($key);
    Cache::load(sprintf('%s', $key));
    Cache::load(implode(':', $tags));
    Cache::clearTags([...$tags]);
    Cache::clearTags($tags);
    RuntimeCache::set('a:b', null);
    $pool->getItem('a:b');
    Cache::load('' . $key);
    Cache::load(sprintf('%s', 'bad:'));
    Cache::load(sprintf('%s', ''));
    Cache::load(sprintf(''));
    Cache::load('' . '');
    Cache::clearTags([...['spread:'], 'literal:']);
    Cache::load(random_int(0, 1) === 1 ? '' : null);
}
