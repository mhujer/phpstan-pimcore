<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleNotRestored;

use Pimcore\Model\DataObject;

function flipWithoutRestore(): void
{
    DataObject::setHideUnpublished(true);
}
