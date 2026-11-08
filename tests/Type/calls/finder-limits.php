<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Calls\FinderLimits;

use Pimcore\Model\DataObject\Product;
use stdClass;

/**
 * @param array<string, mixed> $config
 */
function limitsPimcoreAccepts(array $config, int $limit): void
{
    Product::getBySku('a', 1);
    Product::getBySku('a', $limit);
    Product::getBySku('a', null);
    Product::getBySku('a', '1');
    Product::getBySku('a', limit: true);
    Product::getBySku('a', ['limit' => 1]);
    Product::getBySku('a', ['limit' => '1']);
    Product::getBySku('a', $config);
    Product::getByTitle('a', 'en', true);
}

function limitsPimcoreRejects(): void
{
    Product::getBySku('a', 'abc');
    Product::getBySku('a', new stdClass());
}

function findersThatDontExist(): void
{
    Product::getByNosuch('a', true);
    Product::getbysku('a', true);
}
