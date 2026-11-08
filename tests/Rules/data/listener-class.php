<?php

declare(strict_types=1);

namespace ListenerClass;

use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\AssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DataObjectEvents::POST_UPDATE, method: 'update')]
final class Explicit
{
    public function update(AssetEvent $event): void
    {
        unset($event);
    }
}

#[AsEventListener(DataObjectEvents::POST_UPDATE)]
final class DefaultMethod
{
    public function onPimcoreDataobjectPostUpdate(AssetEvent $event): void
    {
        unset($event);
    }
}

#[AsEventListener(DataObjectEvents::POST_UPDATE)]
final class Invokable
{
    public function __invoke(AssetEvent $event): void
    {
        unset($event);
    }
}

#[AsEventListener(event: DataObjectEvents::POST_UPDATE, method: 'absent')]
final class MissingMethod
{
}
