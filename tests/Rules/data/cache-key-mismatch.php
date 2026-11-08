<?php

declare(strict_types=1);

namespace CacheKeyMismatch;

use Pimcore\Cache;

function differentConstants(): void
{
    $key = 'load_key';
    $data = Cache::load($key);
    Cache::save($data, 'save_key');
}
