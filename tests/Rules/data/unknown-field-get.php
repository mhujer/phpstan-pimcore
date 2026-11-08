<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\UnknownFieldGet;

use Pimcore\Model\DataObject\Product;

function getMatchesTheGetterCaseInsensitively(Product $p): void
{
    $p->get('sku');
    $p->get('SKU');
    $p->get('id');
    $p->get('title', 'en');
    $p->get('nosuch');
}
