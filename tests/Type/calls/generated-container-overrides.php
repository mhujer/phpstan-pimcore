<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\GeneratedContainerOverrides;

use App\Model\SpecialProduct;
use Pimcore\Model\DataObject\Product;
use function PHPStan\Testing\assertType;

assertType('App\Model\Product|null', Product::getById(1));
assertType('App\Model\Product|null', Product::getBySku('a', 1));
assertType('list<App\Model\Product>', Product::getList()->load());
assertType('null', SpecialProduct::getById(1));
