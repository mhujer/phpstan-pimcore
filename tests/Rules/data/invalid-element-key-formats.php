<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Asset;
use Pimcore\Model\Document;

function keyFormats(Asset $asset, Document $document, string $dynamic): void
{
    $asset->setKey(sprintf('folder/%s', $dynamic));
    $document->setKey(sprintf(format: '%s?query', values: $dynamic));
    $asset->setKey(sprintf('%s-valid', $dynamic));
    $document->setKey(sprintf('%2$s-%1$s', $dynamic, $dynamic));
    $asset->setKey(sprintf('%s%%', $dynamic));
}
