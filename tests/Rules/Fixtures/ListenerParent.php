<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Event\Model\AssetEvent;

abstract class ListenerParent
{
    public function update(AssetEvent $event): void
    {
        unset($event);
    }
}
