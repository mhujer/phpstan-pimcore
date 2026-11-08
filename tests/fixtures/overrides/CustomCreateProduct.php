<?php

declare(strict_types=1);

namespace App\Model;

use Pimcore\Model\DataObject\Product;

class CustomCreateProduct extends Product
{
    public static function create(array $values = []): static
    {
        return new static();
    }
}
