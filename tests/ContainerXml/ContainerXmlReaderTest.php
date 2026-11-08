<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use InvalidArgumentException;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\TestCase;

final class ContainerXmlReaderTest extends TestCase
{
    public function testConfigSubtreesAndUntypedScalarsAreAvailable(): void
    {
        $reader = new ContainerXmlReader(self::writeXml(<<<'XML'
            <container xmlns="http://symfony.com/schema/dic/services"><parameters>
            <parameter key="pimcore.config" type="collection">
                <parameter key="general" type="collection">
                    <parameter key="valid_languages">en,de</parameter>
                    <parameter key="enabled">true</parameter>
                    <parameter key="count">12</parameter>
                    <parameter key="ratio">1.5</parameter>
                    <parameter key="numeric_string" type="string">012</parameter>
                    <parameter key="nothing" type="null"/>
                </parameter>
            </parameter>
            <parameter key="pimcore.documents.classes.map" type="collection"><parameter key="page" type="string">App\Page</parameter></parameter>
            <parameter key="pimcore.assets.classes.map" type="collection"><parameter key="image" type="string">App\Image</parameter></parameter>
            </parameters></container>
            XML));

        self::assertSame(['valid_languages' => 'en,de', 'enabled' => true, 'count' => 12, 'ratio' => 1.5, 'numeric_string' => '012', 'nothing' => null], $reader->config(['general']));
        self::assertSame(['page' => 'App\Page'], $reader->parameter('pimcore.documents.classes.map'));
        self::assertSame(['image' => 'App\Image'], $reader->parameter('pimcore.assets.classes.map'));
    }

    public function testUnparseableExplicitXmlReportsOnlyItsPath(): void
    {
        $path = self::writeXml('<container>SECRET');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot read Pimcore Container XML "' . $path . '".');
        (new ContainerXmlReader($path))->config([]);
    }

    public function testExplicitXmlWithoutPimcoreConfigReportsOnlyItsPath(): void
    {
        $path = self::writeXml('<container><parameters><parameter key="secret">SECRET</parameter></parameters></container>');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot read Pimcore Container XML "' . $path . '".');
        (new ContainerXmlReader($path))->config([]);
    }

    public function testBrokenBorrowedXmlIsQuietlyDisabled(): void
    {
        $project = sys_get_temp_dir() . '/pimcore-xml-container-' . bin2hex(random_bytes(8));
        mkdir($project);
        file_put_contents($project . '/config.neon', 'parameters:
    symfony:
        containerXmlPath: missing.xml
parametersSchema:
    symfony: structure([containerXmlPath: string()])
');
        $container = (new ContainerFactory($project))->create($project . '/cache', [$project . '/config.neon'], []);
        $reader = new ContainerXmlReader(null, $container);

        self::assertNull($reader->config([]));
        self::assertStringContainsString('source: symfony', $reader->diagnosis());
        self::assertStringContainsString('disabled: file is missing or unreadable', $reader->diagnosis());
    }

    public function testMissingConfigKeyDisablesOnlyThatLookupAndXmlIsReadOnce(): void
    {
        $path = self::writeXml('<container><parameters><parameter key="pimcore.config" type="collection"><parameter key="known">yes</parameter></parameter></parameters></container>');
        $reader = new ContainerXmlReader($path);
        self::assertNull($reader->config(['missing']));
        unlink($path);
        self::assertSame('yes', $reader->config(['known']));
    }

    public function testServiceAliasesListenersAndWorkflowArgumentsAreAvailable(): void
    {
        $reader = new ContainerXmlReader(self::writeXml(<<<'XML'
            <container><parameters><parameter key="pimcore.config" type="collection"/></parameters><services>
                <service id="listener" class="App\Listener"/>
                <service id="alias" alias="listener"/>
                <service id="abstract" class="App\AbstractListener" abstract="true"/>
                <service id="factory" class="App\FactoryListener"><factory service="maker" method="make"/></service>
                <service id="cycle" alias="cycle"/>
                <service id="debug.event_dispatcher"><call method="addListener">
                    <argument>pimcore.dataobject.postUpdate</argument>
                    <argument type="collection"><argument type="service_closure" id="alias"/><argument>onUpdate</argument></argument>
                    <argument>10</argument>
                </call></service>
                <service id="security.event_dispatcher.main"><call method="addListener"><argument>ignored</argument></call></service>
                <service id="workflow.editorial.definition" class="Symfony\Component\Workflow\Definition">
                    <argument type="collection"><argument>draft</argument><argument>published</argument></argument>
                    <argument type="collection"><argument type="service" id="transition.publish"/></argument>
                </service>
                <service id="transition.publish" class="Symfony\Component\Workflow\Transition"><argument>publish</argument></service>
            </services></container>
            XML));

        self::assertSame('App\Listener', $reader->serviceClass('alias'));
        self::assertNull($reader->serviceClass('cycle'));
        self::assertNull($reader->serviceClass('abstract'));
        self::assertNull($reader->serviceClass('factory'));
        self::assertSame([['pimcore.dataobject.postUpdate', [['type' => 'service_closure', 'id' => 'alias'], 'onUpdate'], 10]], $reader->listeners());
        self::assertSame(['draft', 'published'], $reader->workflows()['workflow.editorial.definition'][0]);
        $transition = $reader->service('transition.publish');
        self::assertNotNull($transition);
        self::assertSame(['publish'], $transition['arguments']);
    }

    public function testResolvedServicesIncludeEveryDependencyEvenWhenUnresolvable(): void
    {
        $reader = new ContainerXmlReader(self::writeXml(<<<'XML'
            <container><parameters><parameter key="pimcore.config" type="collection"/></parameters><services>
                <service id="alias" alias="target"/>
                <service id="123" alias="target"/>
                <service id="target" class="App\Listener"><argument>value</argument></service>
                <service id="missing.alias" alias="missing"/>
                <service id="cycle.a" alias="cycle.b"/>
                <service id="cycle.b" alias="cycle.a"/>
                <service id="abstract.alias" alias="abstract"/>
                <service id="abstract" class="App\Listener" abstract="true"/>
                <service id="factory.alias" alias="factory"/>
                <service id="factory" class="App\Listener"><factory service="maker" method="make"/></service>
            </services></container>
            XML));

        self::assertSame([
            'service' => ['class' => 'App\Listener', 'alias' => null, 'abstract' => false, 'factory' => false, 'arguments' => ['value']],
            'ids' => ['alias', 'target'],
        ], $reader->resolveService('alias'));
        self::assertSame(['123', 'target'], $reader->resolveService('123')['ids']);
        self::assertSame(['service' => null, 'ids' => ['missing.alias', 'missing']], $reader->resolveService('missing.alias'));
        self::assertSame(['service' => null, 'ids' => ['cycle.a', 'cycle.b']], $reader->resolveService('cycle.a'));
        self::assertSame(['service' => null, 'ids' => ['abstract.alias', 'abstract']], $reader->resolveService('abstract.alias'));
        self::assertSame(['service' => null, 'ids' => ['factory.alias', 'factory']], $reader->resolveService('factory.alias'));
    }

    public static function writeXml(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pimcore-xml-');
        self::assertNotFalse($path);
        file_put_contents($path, $xml);

        return $path;
    }
}
