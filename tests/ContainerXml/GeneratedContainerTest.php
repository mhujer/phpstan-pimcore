<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPUnit\Framework\TestCase;

final class GeneratedContainerTest extends TestCase
{
    public function testConfiguredLanguagesSurviveCompilation(): void
    {
        $reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');

        self::assertSame(['en', 'de'], $reader->config(['general', 'valid_languages']));
    }

    public function testRegisteredClassOverrideSurvivesCompilation(): void
    {
        $reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');

        self::assertSame(['Pimcore\\Model\\DataObject\\Product' => 'App\\Model\\Product'], $reader->config(['models', 'class_overrides']));
    }

    public function testDefaultDocumentAndAssetTypeMapsSurviveCompilation(): void
    {
        $reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');

        self::assertSame('\\Pimcore\\Model\\Document\\Page', $reader->parameter('pimcore.documents.classes.map', ['page']));
        self::assertSame('\\Pimcore\\Model\\Asset\\Image', $reader->parameter('pimcore.assets.classes.map', ['image']));
    }

    public function testYamlAttributeAndSubscriberWiringSurvivesCompilation(): void
    {
        $reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');
        $listeners = $reader->listeners();

        foreach ([
            ['pimcore.dataobject.preUpdate', 'App\\Fixture\\YamlListener', 'onUpdate'],
            ['pimcore.dataobject.postUpdate', 'App\\Fixture\\AttributeListener', 'onUpdate'],
            ['pimcore.dataobject.postAdd', 'App\\Fixture\\Subscriber', 'onAdd'],
        ] as [$event, $class, $method]) {
            self::assertContains([$event, [['type' => 'service_closure', 'id' => $class], $method], 0], $listeners);
            self::assertSame($class, $reader->serviceClass($class));
        }
    }

    public function testWorkflowPlacesAndInlineTransitionsSurviveCompilation(): void
    {
        $reader = new ContainerXmlReader(__DIR__ . '/../fixtures/container/generated/' . Fixtures::line() . '.xml');
        $definition = $reader->workflows()['workflow.product_editorial.definition'] ?? null;
        self::assertNotNull($definition);
        self::assertSame(['draft', 'review', 'published'], $definition[0]);
        self::assertIsArray($definition[1]);
        self::assertCount(2, $definition[1]);
        foreach ($definition[1] as $index => $transition) {
            self::assertIsArray($transition);
            self::assertSame('Pimcore\\Workflow\\Transition', $transition['class']);
            self::assertIsArray($transition['arguments']);
            self::assertSame(['submit', 'publish'][$index], $transition['arguments'][0]);
        }
        self::assertSame(['Pimcore\\Model\\DataObject\\Product'], $reader->parameter('pimcore.workflow', ['product_editorial', 'supports']));
    }
}
