<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data;

use Pimcore\Model\Element\Service;
use Pimcore\Model\User;

Service::isValidPath(type: 'objects', path: '/valid');
Service::getElementCacheTag('objects', 1);
Service::getSessionKey('objects', 1, 'session');
Service::getElementFromSession('objects', 1, 'session');
Service::removeElementFromSession('objects', 1, 'session');
Service::findForbiddenPaths('objects', new User());

function extendedTypes(string $dynamic): void
{
    Service::getValidKey('valid', $dynamic);
    Service::isValidKey('valid', 'object');
    Service::isValidPath('/valid', 'document');
    Service::getElementCacheTag('asset', 1);
    Service::getSessionKey('document', 1, 'session');
    Service::getElementFromSession('object', 1, 'session');
    Service::removeElementFromSession('asset', 1, 'session');
    Service::findForbiddenPaths('document', new User());
}
