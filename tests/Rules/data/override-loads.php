<?php

declare(strict_types=1);

use App\Model\AbstractProduct;
use App\Model\ChildProduct;
use App\Model\IntermediateProduct;
use App\Model\NativeProduct;
use App\Model\Product;
use App\Model\RegisteredProduct;

ChildProduct::getById(1);
ChildProduct::getByPath('/p');
ChildProduct::getBySku('a', 1);
ChildProduct::getList();
Product::getById(1);
RegisteredProduct::getBySku('a', 1);
IntermediateProduct::getList();
AbstractProduct::getList();
NativeProduct::getBySku('a');
NativeProduct::getList();
ChildProduct::getList(['class' => 'Other']);
ChildProduct::getbyid(1);
ChildProduct::GETBYPATH('/p');
