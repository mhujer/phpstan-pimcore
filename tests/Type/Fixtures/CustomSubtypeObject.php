<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\DataObject;

final class CustomSubtypeObject extends DataObject\Folder
{
    public function getType(): string
    {
        return 'custom';
    }
}
