<?php

declare(strict_types=1);

namespace App\Model;

class NativeProduct extends Product
{
    public static function getBySku(mixed $value, ?int $limit = null): \Pimcore\Model\DataObject\Product|\Pimcore\Model\DataObject\Product\Listing|null
    {
        return null;
    }

    public static function getList(array $config = []): \Pimcore\Model\DataObject\Product\Listing
    {
        return new \Pimcore\Model\DataObject\Product\Listing();
    }
}
