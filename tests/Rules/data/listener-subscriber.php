<?php

declare(strict_types=1);

namespace ListenerSubscriber;

use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\ListenerParent;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\DocumentEvents;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class Subscriber extends ListenerParent implements EventSubscriberInterface
{
    /** @return array<string, mixed> */
    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::POST_ADD => 'update',
            DataObjectEvents::POST_UPDATE => ['update', 100],
            DataObjectEvents::POST_DELETE => [['update', 100], ['other', -5]],
            DocumentEvents::POST_UPDATE => 'object',
            DataObjectEvents::PRE_UPDATE => 'object',
            DataObjectEvents::PRE_ADD => 'missing',
        ];
    }

    public function other(AssetEvent $event): void
    {
        unset($event);
    }

    public function object(DataObjectEvent $event): void
    {
        unset($event);
    }
}

final class DynamicSubscriber implements EventSubscriberInterface
{
    /** @return array<string, mixed> */
    public static function getSubscribedEvents(): array
    {
        return json_decode('{}', true);
    }
}

final class Ordinary
{
    /** @return array<string, mixed> */
    public static function getSubscribedEvents(): array
    {
        return [DataObjectEvents::POST_UPDATE => 'update'];
    }

    public function update(AssetEvent $event): void
    {
        unset($event);
    }
}
