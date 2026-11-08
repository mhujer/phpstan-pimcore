<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ContainerXml;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use PHPUnit\Framework\TestCase;

final class ContainerXmlCacheTest extends TestCase
{
    public function testOnlyTheRequestedSubtreeChangesItsCacheValue(): void
    {
        $xml = '<container><parameters><parameter key="pimcore.config" type="collection"><parameter key="general" type="collection"><parameter key="valid_languages">en</parameter></parameter><parameter key="secret">SECRET</parameter></parameter></parameters></container>';
        $key = ContainerXmlCache::key('parameter', 'pimcore.config', 'general', 'valid_languages');
        $before = new ContainerXmlCache(new ContainerXmlReader(ContainerXmlReaderTest::writeXml($xml)));
        $unrelated = new ContainerXmlCache(new ContainerXmlReader(ContainerXmlReaderTest::writeXml(str_replace('SECRET', 'OTHER', $xml))));
        $changed = new ContainerXmlCache(new ContainerXmlReader(ContainerXmlReaderTest::writeXml(str_replace('>en<', '>de<', $xml))));

        self::assertSame($before->getValue($key), $unrelated->getValue($key));
        self::assertNotSame($before->getValue($key), $changed->getValue($key));
        self::assertSame($key, $before->keyFromResultCache($before->keyToResultCache($key)));
    }
}
