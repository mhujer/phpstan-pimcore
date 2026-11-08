<?php

declare(strict_types=1);

namespace ListenerXmlDeclarations;

use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\AssetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class Attributed
{
    #[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
    public function update(AssetEvent $event): void
    {
        $event->getAsset();
    }
}

final class Subscribed implements EventSubscriberInterface
{
    public function update(AssetEvent $event): void
    {
        $event->getAsset();
    }

    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return [DataObjectEvents::POST_UPDATE => 'UPDATE'];
    }
}

#[AsEventListener(event: DataObjectEvents::POST_UPDATE, method: 'update')]
final class ClassAttributed
{
    public function update(AssetEvent $event): void
    {
        $event->getAsset();
    }
}
