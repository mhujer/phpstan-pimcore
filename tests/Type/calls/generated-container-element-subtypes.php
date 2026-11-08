<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\GeneratedContainerElementSubtypes;

use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use function PHPStan\Testing\assertType;

function generatedSubtypes(Document $document, Asset $asset, Document\Page $page, Asset\Image $image): void
{
    assertType("'email'|'folder'|'hardlink'|'link'|'page'|'snippet'", $document->getType());
    assertType("'archive'|'audio'|'document'|'folder'|'image'|'text'|'unknown'|'video'", $asset->getType());
    assertType("'page'", $page->getType());
    assertType("'image'", $image->getType());
    assertType('Pimcore\\Model\\Document|null', Document::getById(1));
}
