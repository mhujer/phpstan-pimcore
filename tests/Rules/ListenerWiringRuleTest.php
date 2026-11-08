<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ListenerWiringRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ListenerWiringRule> */
final class ListenerWiringRuleTest extends RuleTestCase
{
    public function testMethodAttribute(): void
    {
        $this->analyse([__DIR__ . '/data/listener-method.php'], [
            ['Listener ListenerMethod\\Listener::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 13],
        ]);
    }

    public function testClassAttributes(): void
    {
        $this->analyse([__DIR__ . '/data/listener-class.php'], [
            ['Listener ListenerClass\\Explicit::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 11],
            ['Listener ListenerClass\\DefaultMethod::onPimcoreDataobjectPostUpdate() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 20],
            ['Listener ListenerClass\\Invokable::__invoke() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 29],
        ]);
    }

    public function testAttributesWithoutEventNames(): void
    {
        $this->analyse([__DIR__ . '/data/listener-without-name.php'], [
            ['#[AsEventListener] on ListenerWithoutName\\Invokable::__invoke() has no event name, so Symfony listens for Pimcore\\Event\\Model\\DataObjectEvent, which Pimcore never dispatches. Set event:, e.g. DataObjectEvents::POST_UPDATE.', 14],
            ['#[AsEventListener] on ListenerWithoutName\\Explicit::update() has no event name, so Symfony listens for Pimcore\\Event\\Model\\ElementEventInterface, which Pimcore never dispatches. Set event:, e.g. DataObjectEvents::POST_UPDATE.', 23],
            ['#[AsEventListener] on ListenerWithoutName\\Methods::update() has no event name, so Symfony listens for Pimcore\\Event\\Model\\DataObjectEvent, which Pimcore never dispatches. Set event:, e.g. DataObjectEvents::POST_UPDATE.', 34],
        ]);
    }

    public function testConstantArraySubscribers(): void
    {
        $this->analyse([__DIR__ . '/data/listener-subscriber.php'], [
            ['Listener ListenerSubscriber\\Subscriber::update() for event pimcore.dataobject.postAdd expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 17],
            ['Listener ListenerSubscriber\\Subscriber::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 17],
            ['Listener ListenerSubscriber\\Subscriber::update() for event pimcore.dataobject.postDelete expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 17],
            ['Listener ListenerSubscriber\\Subscriber::other() for event pimcore.dataobject.postDelete expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 17],
            ['Listener ListenerSubscriber\\Subscriber::object() for event pimcore.document.postUpdate expects Pimcore\\Event\\Model\\DataObjectEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DocumentEvent.', 17],
        ]);
    }

    // The source fixture retains an untyped parameter that ECS would otherwise add a type to.
    public function testConservativeExclusionsAndGenericEvents(): void
    {
        $this->analyse([__DIR__ . '/data/listener-exclusions.php.fixture'], [
            ['Listener ListenerExclusions\\Listener::generic() for event pimcore.cache.core.init expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Symfony\\Component\\EventDispatcher\\GenericEvent.', 63],
        ]);
    }

    public function testDiagnosticIdentifiers(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/listener-method.php', __DIR__ . '/data/listener-without-name.php']);
        $identifiers = array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors)));
        sort($identifiers);
        self::assertSame(['pimcore.listenerEventType', 'pimcore.listenerWithoutEventName'], $identifiers);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ListenerWiringRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
