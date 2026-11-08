<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\NeverType;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

final class MetadataMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->hasNativeMethod($methodName)
            || (!$classReflection->is(ObjectMetadata::class) && !$classReflection->is(ElementMetadata::class))
            || strlen($methodName) <= 3
            || (!str_starts_with($methodName, 'get') && !str_starts_with($methodName, 'set'))
        ) {
            return false;
        }

        $columns = $classReflection->getActiveTemplateTypeMap()->getType('TColumn');
        if ($columns === null || !$columns instanceof NeverType && $columns->isString()->yes() && $columns->getConstantStrings() === []) {
            return true;
        }

        foreach ($columns->getConstantStrings() as $column) {
            if (strcasecmp($column->getValue(), substr($methodName, 3)) === 0) {
                return true;
            }
        }

        return false;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new MetadataMethodReflection($classReflection, $methodName, str_starts_with($methodName, 'set'), null);
    }
}
