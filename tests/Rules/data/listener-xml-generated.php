<?php

declare(strict_types=1);

namespace App\Fixture;

use Pimcore\Event\Model\AssetEvent;

final class YamlListener
{
    public function onUpdate(AssetEvent $event): void
    {
        $event->getAsset();
    }
}
