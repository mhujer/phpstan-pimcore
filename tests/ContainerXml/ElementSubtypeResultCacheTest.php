<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class ElementSubtypeResultCacheTest extends TestCase
{
    public function testTypeMapsAndClassOverridesInvalidateCachedReturnTypes(): void
    {
        $project = Phpstan::createProject();
        mkdir($project . '/src');
        file_put_contents($project . '/src/check.php', <<<'PHP'
            <?php
            function subtypes(\Pimcore\Model\Document $document, \Pimcore\Model\Document\Snippet $snippet, \Pimcore\Model\Asset $asset): void {
                \PHPStan\dumpType($document->getType());
                \PHPStan\dumpType($snippet->getType());
                \PHPStan\dumpType($asset->getType());
            }
            PHP);
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
            parameters:
                level: 0
                paths:
                    - src
                tmpDir: cache
                pimcore:
                    containerXmlPath: container.xml
            NEON, Phpstan::root()));
        $xml = <<<'XML'
            <container><parameters>
              <parameter key="pimcore.config" type="collection">
                <parameter key="models" type="collection"><parameter key="class_overrides" type="collection">
                  <parameter key="Pimcore\Model\Document">Pimcore\Model\Document\Page</parameter>
                </parameter></parameter>
              </parameter>
              <parameter key="pimcore.documents.classes.map" type="collection">
                <parameter key="page">Pimcore\Model\Document</parameter>
                <parameter key="snippet">Pimcore\Model\Document\Snippet</parameter>
              </parameter>
              <parameter key="pimcore.assets.classes.map" type="collection">
                <parameter key="image">Pimcore\Model\Asset\Image</parameter>
                <parameter key="text">Pimcore\Model\Asset\Text</parameter>
              </parameter>
            </parameters></container>
            XML;
        file_put_contents($project . '/container.xml', $xml);
        $expected = ["Dumped type: 'page'|'snippet'", "Dumped type: 'snippet'", "Dumped type: 'image'|'text'"];
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));

        // No source file changes: each family map is an independent cache input.
        file_put_contents($project . '/container.xml', str_replace('key="page"', 'key="article"', $xml));
        $changed = $expected;
        $changed[0] = "Dumped type: 'article'|'snippet'";
        self::assertSame($changed, array_column(Phpstan::analyse($project), 'message'));
        file_put_contents($project . '/container.xml', str_replace('key="image"', 'key="photo"', $xml));
        $changed = $expected;
        $changed[2] = "Dumped type: 'photo'|'text'";
        self::assertSame($changed, array_column(Phpstan::analyse($project), 'message'));

        // The map stays identical; changing its class override changes the matching receiver.
        file_put_contents($project . '/container.xml', str_replace('>Pimcore\Model\Document\Page<', '>Pimcore\Model\Document\Snippet<', $xml));
        $changed = $expected;
        $changed[1] = "Dumped type: 'page'|'snippet'";
        self::assertSame($changed, array_column(Phpstan::analyse($project), 'message'));

        // Missing keys disable only this feature, and reappearing keys must invalidate that fallback.
        file_put_contents($project . '/container.xml', '<container><parameters><parameter key="pimcore.config" type="collection"/></parameters></container>');
        self::assertSame(array_fill(0, 3, 'Dumped type: string'), array_column(Phpstan::analyse($project), 'message'));
        file_put_contents($project . '/container.xml', $xml);
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
    }
}
