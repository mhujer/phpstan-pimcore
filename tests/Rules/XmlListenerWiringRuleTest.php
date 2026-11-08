<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Rules\ListenerWiringCollector;
use Mhujer\PHPStan\Pimcore\Rules\ListenerWiringRule;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PhpParser\Node;
use PHPStan\Analyser\Error;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<ListenerWiringRule> */
final class XmlListenerWiringRuleTest extends RuleTestCase
{
    private ?ContainerXmlReader $reader = null;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/data/listener-xml.php';
        require_once __DIR__ . '/data/listener-xml-declarations.php';
        require_once __DIR__ . '/data/listener-xml-generated.php';
        require_once __DIR__ . '/data/listener-xml-inherited.php';
    }


    public function testWiredListenerIsReportedAtItsMethod(): void
    {
        $this->analyse([__DIR__ . '/data/listener-xml.php'], [
            ['Listener ListenerXml\\Listener::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 11],
        ]);
    }

    public function testOwnDeclarationsAreReportedOnlyOnce(): void
    {
        $this->analyse([__DIR__ . '/data/listener-xml-declarations.php'], [
            ['Listener ListenerXmlDeclarations\\Attributed::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 14],
            ['Listener ListenerXmlDeclarations\\Subscribed::update() for event pimcore.dataobject.postAdd expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 23],
            ['Listener ListenerXmlDeclarations\\Subscribed::UPDATE() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 29],
            ['Listener ListenerXmlDeclarations\\ClassAttributed::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 35],
        ]);
    }

    public function testWithoutXmlKeepsExistingBehavior(): void
    {
        $this->reader = new ContainerXmlReader(null);
        $this->analyse([__DIR__ . '/data/listener-xml.php'], []);
        $this->analyse([__DIR__ . '/data/listener-xml-declarations.php'], [
            ['Listener ListenerXmlDeclarations\\Attributed::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 14],
            ['Listener ListenerXmlDeclarations\\Subscribed::UPDATE() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 29],
            ['Listener ListenerXmlDeclarations\\ClassAttributed::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 35],
        ]);
    }

    public function testInstalledGeneratedListenerShape(): void
    {
        $this->reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');
        $this->analyse([__DIR__ . '/data/listener-xml-generated.php'], [
            ['Listener App\\Fixture\\YamlListener::onUpdate() for event pimcore.dataobject.preUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 11],
        ]);
    }

    public function testInheritedCallbacksAreCheckedAtTheirDeclarationOnce(): void
    {
        $this->analyse([__DIR__ . '/data/listener-xml-inherited.php'], [
            ['Listener ListenerXmlInherited\\BaseListener::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 12],
            ['Listener ListenerXmlInherited\\ChildListener::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 21],
        ]);
    }

    public function testSubscriberDeclarationSuppressesItsInheritedXmlCallback(): void
    {
        $xml = file_get_contents(__DIR__ . '/../fixtures/container/listeners.xml');
        self::assertIsString($xml);
        $path = tempnam(sys_get_temp_dir(), 'pimcore-listener-inherited-');
        self::assertNotFalse($path);
        file_put_contents($path, str_replace('class="ListenerXmlInherited\\OtherListener"', 'class="UnknownListener"', $xml));
        $this->reader = new ContainerXmlReader($path);
        $this->analyse([__DIR__ . '/data/listener-xml-inherited.php'], [
            ['Listener ListenerXmlInherited\\ChildListener::update() for event pimcore.dataobject.postUpdate expects Pimcore\\Event\\Model\\AssetEvent, but Pimcore dispatches Pimcore\\Event\\Model\\DataObjectEvent.', 21],
        ]);
    }

    public function testDiagnosticIdentifier(): void
    {
        self::assertSame(['pimcore.listenerEventType'], array_map(
            static fn (Error $error): ?string => $error->getIdentifier(),
            $this->gatherAnalyserErrors([__DIR__ . '/data/listener-xml.php']),
        ));
    }

    /** @return list<Collector<Node, mixed>> */
    protected function getCollectors(): array
    {
        return [new ListenerWiringCollector($this->getRule())];
    }

    protected function getRule(): Rule
    {
        return new ListenerWiringRule($this->reader ?? new ContainerXmlReader(__DIR__ . '/../fixtures/container/listeners.xml'), self::getContainer()->getByType(ReflectionProvider::class));
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
