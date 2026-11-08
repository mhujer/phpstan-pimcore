<?php

declare(strict_types=1);

namespace ListenerWithoutName;

use DateTimeInterface;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\EventDispatcher\Event;

#[AsEventListener]
final class Invokable
{
    public function __invoke(DataObjectEvent $event): void
    {
        unset($event);
    }
}

#[AsEventListener(method: 'update')]
final class Explicit
{
    public function update(ElementEventInterface $event): void
    {
        unset($event);
    }
}

final class Methods
{
    #[AsEventListener]
    public function update(DataObjectEvent $event): void
    {
        unset($event);
    }

    #[AsEventListener]
    public function generic(GenericEvent $event): void
    {
        unset($event);
    }

    #[AsEventListener]
    public function symfony(Event $event): void
    {
        unset($event);
    }

    #[AsEventListener]
    public function other(DateTimeInterface $event): void
    {
        unset($event);
    }
}
