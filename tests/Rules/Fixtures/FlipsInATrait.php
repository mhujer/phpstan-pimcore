<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

trait FlipsInATrait
{
    public function flip(): void
    {
        DataObject::setHideUnpublished(true);
    }
}
