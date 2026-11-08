<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\Service;

final class FolderCreationReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Service::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === 'createfolderbypath';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if (!$methodCall->class instanceof Name || $methodReflection->getDeclaringClass()->getName() !== Service::class) {
            return null;
        }

        $folderClass = match (strtolower($scope->resolveName($methodCall->class))) {
            strtolower(Asset\Service::class) => Asset\Folder::class,
            strtolower(DataObject\Service::class) => DataObject\Folder::class,
            strtolower(Document\Service::class) => Document\Folder::class,
            default => null,
        };

        return $folderClass === null ? null : TypeCombinator::union(new ObjectType($folderClass), new NullType());
    }
}
