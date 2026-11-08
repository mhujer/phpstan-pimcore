<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Element\Service;
use Pimcore\Model\Element\Tag;

Service::getElementById('dataobject', 1);
Service::getElementByPath('Object', '/');
Service::pathExists('/', 'objects');
Tag::addTagToElement('dataObject', 1, new Tag());
Tag::getTagsForElement('assets', 1);
Tag::removeTagFromElement('documents', 1, new Tag());
Tag::setTagsForElement('wrong', 1, []);
Tag::batchAssignTagsToElement('Object', [1], []);
Service::getElementById(type: 'ASSET', id: 1);

/**
 * @param 'asset'|'objects' $union
 */
function elementTypes(string $union, string $unknown): void
{
    Service::getElementById($union, 1);
    Service::getElementById($unknown, 1);
    Service::getElementByPath($unknown, '/');
    Service::pathExists('/');
    Service::pathExists('/', null);
    Service::pathExists('/', $unknown);
    Service::getValidKey('key', 'folder');
    Service::isValidKey('key', 'folder');
    foreach (['asset', 'document', 'object'] as $valid) {
        Service::getElementById($valid, 1);
        Service::getElementByPath($valid, '/');
        Service::pathExists('/', $valid);
        Tag::getTagsForElement($valid, 1);
    }
}
