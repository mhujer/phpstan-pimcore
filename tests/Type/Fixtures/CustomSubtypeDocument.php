<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\Document\Page;

final class CustomSubtypeDocument extends Page
{
    public function getType(): string
    {
        return 'custom';
    }
}
