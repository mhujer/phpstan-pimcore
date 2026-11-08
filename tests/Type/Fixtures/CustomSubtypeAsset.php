<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\Asset\Image;

final class CustomSubtypeAsset extends Image
{
    public function getType(): string
    {
        return 'custom';
    }
}
