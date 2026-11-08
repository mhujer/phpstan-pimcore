<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

final class ConfigProduct extends DataObject
{
    /** @param array<string, mixed> $config */
    public static function getList(array $config = []): DataObject\Listing
    {
        return parent::getList($config);
    }
}
