<?php

declare(strict_types=1);

namespace App\Model;

use Pimcore\Model\DataObject\Product;

class CustomValuesProduct extends Product
{
    public function setValue(string $key, mixed $value, bool $ignoreEmptyValues = false): static
    {
        return $this;
    }
}
