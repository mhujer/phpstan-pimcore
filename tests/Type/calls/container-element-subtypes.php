<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\ContainerElementSubtypes;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomSubtypeAsset;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomSubtypeDocument;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\SubtypeImageOverride;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\SubtypePageOverride;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use function PHPStan\Testing\assertType;

function mappedSubtypes(Document $document, Asset $asset): void
{
    assertType("'page'|'snippet'", $document->getType());
    assertType("'image'|'text'", $asset->getType());
    assertType('Pimcore\\Model\\Document|null', Document::getById(1));
}

function mappedClasses(Document\Page $page, Asset\Image $image, SubtypePageOverride $pageOverride, SubtypeImageOverride $imageOverride): void
{
    assertType("'page'", $page->getType());
    assertType("'image'", $image->GETTYPE());
    assertType("'page'", $pageOverride->getType());
    assertType("'image'", $imageOverride->getType());
}

function customGetters(CustomSubtypeDocument $document, CustomSubtypeAsset $asset): void
{
    assertType('string', $document->getType());
    assertType('string', $asset->getType());
}

function compareSubtypes(Document $document, Asset $asset): void
{
    if ($document->getType() === 'page') {
        assertType("'page'", $document->getType());
        assertType('Pimcore\\Model\\Document', $document);
    }
    match ($asset->getType()) {
        'image' => assertType('Pimcore\\Model\\Asset', $asset),
        default => null,
    };
}
