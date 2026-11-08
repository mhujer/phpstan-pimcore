<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class ListenerResultCacheTest extends TestCase
{
    public function testListenerWiringAndAliasedServiceClassesInvalidateCachedDiagnostics(): void
    {
        $project = Phpstan::createProject();
        mkdir($project . '/src');
        copy(__DIR__ . '/../Rules/data/listener-xml.php', $project . '/src/listener.php');
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
                - %s/rules.neon
            parameters:
                level: 0
                paths:
                    - src
                tmpDir: cache
                bootstrapFiles:
                    - %s/tests/bootstrap.php
                pimcore:
                    containerXmlPath: container.xml
            NEON, Phpstan::root(), Phpstan::root(), Phpstan::root()));
        $xml = <<<'XML'
            <container>
              <parameters><parameter key="pimcore.config" type="collection"/></parameters>
              <services>
                <service id="listener" class="ListenerXml\Listener"/>
                <service id="alias" alias="listener"/>
                <service id="debug.event_dispatcher">
                  <call method="addListener">
                    <argument>pimcore.dataobject.postUpdate</argument>
                    <argument type="collection"><argument type="service_closure" id="alias"/><argument>update</argument></argument>
                  </call>
                </service>
              </services>
            </container>
            XML;
        file_put_contents($project . '/container.xml', $xml);
        $expected = [['line' => 11, 'message' => 'Listener ListenerXml\Listener::update() for event pimcore.dataobject.postUpdate expects Pimcore\Event\Model\AssetEvent, but Pimcore dispatches Pimcore\Event\Model\DataObjectEvent.']];
        self::assertSame($expected, Phpstan::analyse($project));
        self::assertSame($expected, Phpstan::analyse($project));

        // The listener call stays identical; only the target behind its alias changes.
        file_put_contents($project . '/container.xml', str_replace('class="ListenerXml\Listener"', 'class="UnknownListener"', $xml));
        self::assertSame([], Phpstan::analyse($project));
        file_put_contents($project . '/container.xml', $xml);
        self::assertSame($expected, Phpstan::analyse($project));

        // The service stays identical; changing the event makes this parameter compatible.
        file_put_contents($project . '/container.xml', str_replace('pimcore.dataobject.postUpdate', 'pimcore.asset.postUpdate', $xml));
        self::assertSame([], Phpstan::analyse($project));
    }
}
