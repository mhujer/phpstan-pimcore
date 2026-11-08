<?php

declare(strict_types=1);

use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document\Page;

Product::getList();
Product::getBySku('a');
Page::getById(1);
Image::getByPath('/a');
new Product\Listing();
new Page();
new Image();
Product::getBySku('a', 1);
