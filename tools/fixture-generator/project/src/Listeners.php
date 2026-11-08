<?php

declare(strict_types=1);

namespace App\Fixture;

use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\DataObjectEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class YamlListener
{
    public function onUpdate(DataObjectEvent $event): void
    {
    }
}

#[AsEventListener(event: DataObjectEvents::POST_UPDATE, method: 'onUpdate')]
final class AttributeListener
{
    public function onUpdate(DataObjectEvent $event): void
    {
    }
}

final class Subscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [DataObjectEvents::POST_ADD => 'onAdd'];
    }

    public function onAdd(DataObjectEvent $event): void
    {
    }
}
