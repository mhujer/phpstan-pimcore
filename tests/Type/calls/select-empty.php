<?php

declare(strict_types=1);

namespace SelectEmptyCalls;

use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Product;

function blockSelect(Product $product): ?string
{
    $row = ($product->getContent() ?? [])[0] ?? [];
    $color = ($row['color'] ?? null) instanceof BlockElement ? $row['color']->getData() : null;

    return is_string($color) && $color !== '' ? $color : null;
}

function select(Product $product): ?string
{
    $color = $product->getColor();

    return is_string($color) && $color !== '' ? $color : null;
}
