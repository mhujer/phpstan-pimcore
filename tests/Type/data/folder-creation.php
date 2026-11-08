<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element;
use function PHPStan\Testing\assertType;

assertType('Pimcore\\Model\\Asset\\Folder|null', Asset\Service::createFolderByPath('/images'));
assertType('Pimcore\\Model\\DataObject\\Folder|null', DataObject\Service::createFolderByPath('/products'));
assertType('Pimcore\\Model\\Document\\Folder|null', Document\Service::createFolderByPath('/pages'));

assertType('Pimcore\\Model\\Asset\\Folder|null', Asset\Service::CREATEFOLDERBYPATH(options: [], path: '/images'));
assertType('Pimcore\\Model\\Asset\\Folder|Pimcore\\Model\\DataObject\\Folder|Pimcore\\Model\\Document\\Folder|null', Element\Service::createFolderByPath('/unknown'));

function folderCreation(string $path): void
{
    $folder = DataObject\Service::createFolderByPath($path);
    if ($folder === null) {
        assertType('null', $folder);
    } else {
        assertType('Pimcore\\Model\\DataObject\\Folder', $folder);
    }
}
