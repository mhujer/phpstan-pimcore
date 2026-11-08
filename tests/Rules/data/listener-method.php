<?php

declare(strict_types=1);

namespace ListenerMethod;

use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\AssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class Listener
{
    #[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
    public function update(AssetEvent $event): void
    {
        unset($event);
    }
}
