<?php

declare(strict_types=1);

namespace ListenerXmlInherited;

use Pimcore\Event\Model\AssetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class BaseListener
{
    public function update(AssetEvent $event): void
    {
        $event->getAsset();
    }
}

final class ChildListener extends BaseListener implements EventSubscriberInterface
{
    /** @return array<string, string> */
    public static function getSubscribedEvents(): array
    {
        return ['pimcore.dataobject.postUpdate' => 'update'];
    }
}

final class OtherListener extends BaseListener
{
}
