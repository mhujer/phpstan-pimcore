<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use App\Model\Product;
use App\Model\SpecialProduct;

use function PHPStan\Testing\assertType;

function overrideFixtures(Product $product, SpecialProduct $specialProduct): void
{
    assertType('string|null', $product->getSku());
    assertType('string|null', $specialProduct->getSku());
}
