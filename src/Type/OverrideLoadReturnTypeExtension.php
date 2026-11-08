<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\AbstractObject;

final class OverrideLoadReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    /** @param class-string $declaringClass */
    public function __construct(private readonly string $declaringClass, private readonly RegisteredClassOverrides $overrides)
    {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['getById', 'getByPath'], true);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if (!$this->overrides->configured($scope)
            || !in_array($methodReflection->getDeclaringClass()->getName(), [$this->declaringClass], true)
        ) {
            return null;
        }
        $called = $methodCall->class instanceof Name
            ? $scope->resolveTypeByName($methodCall->class)
            : $scope->getType($methodCall->class)->getClassStringObjectType();
        if ($methodCall->class instanceof Name && strtolower($methodCall->class->toString()) !== 'static') {
            $called = new ObjectType($scope->resolveName($methodCall->class));
        }
        $types = [];
        foreach ($called->getObjectClassReflections() as $class) {
            $loaded = $this->declaringClass === AbstractObject::class
                ? $this->overrides->loadedClass($class, $scope)
                : $this->overrides->replacement($class->getName(), $scope);
            if ($loaded === null || ($class->isAbstract() && !$loaded->isObject()->yes())) {
                return null;
            }
            $types[] = TypeCombinator::addNull($loaded);
        }

        return $types === [] ? null : TypeCombinator::union(...$types);
    }
}
