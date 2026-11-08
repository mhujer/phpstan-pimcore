<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

enum Status: string
{
    case Active = 'active';
    case Archived = 'archived';
}
