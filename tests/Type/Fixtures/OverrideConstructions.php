<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use App\Model\RegisteredProduct;

class OverrideConstructions extends RegisteredProduct
{
    public static function build(): void
    {
        new static();
        static::create();
        new self();
        self::create();
    }
}
