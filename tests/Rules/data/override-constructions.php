<?php

declare(strict_types=1);

use App\Model\RegisteredProduct;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document\Page;

new Product();
Product::create();
new Product\Listing();
new RegisteredProduct();
RegisteredProduct::create();
new Page();
new Image();
