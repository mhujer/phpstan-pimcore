<?php

declare(strict_types=1);

namespace BlockCalls;

use Pimcore\Model\DataObject\Product;

function keys(Product $product): mixed
{
    $items = $product->getContent() ?? [];
    $item = $items[0];
    if (isset($item['title'])) {
        $item['title']->setData(42);
    }

    return $item['typo'];
}
