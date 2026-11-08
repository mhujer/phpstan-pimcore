<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\DataObject;

final class CustomCopyService extends DataObject\Service
{
    public function copyAsChild(DataObject\AbstractObject $target, DataObject\AbstractObject $source): DataObject\AbstractObject
    {
        return $source->getParent() ?? $target;
    }
}
