<?php

declare(strict_types=1);

namespace ListenerXml;

use Pimcore\Event\Model\AssetEvent;

final class Listener
{
    public function update(AssetEvent $event): void
    {
        $event->getAsset();
    }
}
