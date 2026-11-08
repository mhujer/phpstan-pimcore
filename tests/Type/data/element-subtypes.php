<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\CustomSubtypeObject;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\User;
use function PHPStan\Testing\assertType;

function elementSubtypes(DataObject\AbstractObject $object, User\AbstractUser $user): void
{
    assertType("'folder'|'object'|'variant'", $object->getType());
    assertType("'role'|'rolefolder'|'user'|'userfolder'", $user->getType());
}

function inheritedElementSubtypes(DataObject\Product $product, DataObject\Folder $folder, User $user, User\Role $role, User\Folder $userFolder, User\Role\Folder $roleFolder): void
{
    assertType("'folder'|'object'|'variant'", $product->getType());
    assertType("'folder'|'object'|'variant'", $folder->GETTYPE());
    assertType("'role'|'rolefolder'|'user'|'userfolder'", $user->getType());
    assertType("'role'|'rolefolder'|'user'|'userfolder'", $role->getType());
    assertType("'role'|'rolefolder'|'user'|'userfolder'", $userFolder->getType());
    assertType("'role'|'rolefolder'|'user'|'userfolder'", $roleFolder->getType());
}

function unchangedElementSubtypes(CustomSubtypeObject $custom, Document $document, Asset $asset): void
{
    assertType('string', $custom->getType());
    assertType('string', $document->getType());
    assertType('string', $asset->getType());
}

function compareElementSubtype(DataObject\AbstractObject $object, User\AbstractUser $user): void
{
    if ($object->getType() === 'folder') {
        assertType("'folder'", $object->getType());
        assertType('Pimcore\\Model\\DataObject\\AbstractObject', $object);
    }

    match ($user->getType()) {
        'role' => assertType('Pimcore\\Model\\User\\AbstractUser', $user),
        default => null,
    };
}

function withoutContainerXml(Document\Page $page, Asset\Image $image): void
{
    assertType('string', $page->getType());
    assertType('string', $image->getType());
    assertType('Pimcore\\Model\\Document|null', Document::getById(1));
}
