<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use LogicException;
use PHPStan\PhpDoc\Tag\MethodTag;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Pimcore\Model\DataObject\Concrete;

/**
 * A finder is declared by a `@method` tag of the generated class. This takes the tag's signature, with `$limit` also
 * accepting what Pimcore's `__callStatic()` accepts: a bool or numeric string (`limit: true`, `'1'`) and the array
 * form (`['limit' => 1, 'offset' => 5]`). PHPStan asks class reflection extensions before reading `@method` tags.
 */
final class FinderMethodsClassReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return self::findTag($classReflection, $methodName) !== null;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        $found = self::findTag($classReflection, $methodName)
            ?? throw new LogicException(sprintf('%s::%s() is not a finder.', $classReflection->getName(), $methodName));

        $callStatic = $classReflection->hasNativeMethod('__callStatic') ? $classReflection->getNativeMethod('__callStatic') : null;

        return new FinderMethodReflection($found['declaringClass'], $methodName, $found['tag'], $callStatic?->getThrowType());
    }

    /**
     * The finder's tag and the class that declares it, the nearest one up from `$classReflection`. Names match with
     * the exact case, as for any `@method` tag.
     *
     * @return array{declaringClass: ClassReflection, tag: MethodTag}|null
     */
    private static function findTag(ClassReflection $classReflection, string $methodName): ?array
    {
        if (!str_starts_with($methodName, 'getBy') || !$classReflection->isSubclassOf(Concrete::class)) {
            return null;
        }

        for ($class = $classReflection; $class !== null && $class->getName() !== Concrete::class; $class = $class->getParentClass()) {
            $tag = $class->getMethodTags()[$methodName] ?? null;
            if ($tag === null) {
                continue;
            }

            // Anything that doesn't look like a generated finder is left to PHPStan's own `@method` handling.
            if (!$tag->isStatic() || $tag->getTemplateTags() !== [] || !isset($tag->getParameters()[FinderMethodReflection::LIMIT])) {
                return null;
            }

            return ['declaringClass' => $class, 'tag' => $tag];
        }

        return null;
    }
}
