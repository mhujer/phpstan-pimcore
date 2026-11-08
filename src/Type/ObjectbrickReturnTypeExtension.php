<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Objectbrick;

/**
 * Objectbricks, typed from the generated brick container:
 *
 * - the objectbrick field's getter on a generated class creates the container on first access, so it is non-null;
 * - the container's `getItems()` is a list of the bricks its `$brickGetters` allows.
 *
 * Registered once for `Concrete` (the field getters) and once for `Objectbrick` (`getItems()`).
 */
final class ObjectbrickReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param Concrete::class|Objectbrick::class $declaringClass
     */
    public function __construct(
        private readonly string $declaringClass,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        if ($this->declaringClass === Objectbrick::class) {
            return $methodReflection->getName() === 'getItems';
        }

        $containers = self::brickContainers(TypeCombinator::removeNull(DeclaredReturnType::of($methodReflection)));

        return $containers !== null;
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if ($this->declaringClass === Objectbrick::class) {
            return self::itemsType($scope->getType($methodCall->var), $scope);
        }

        return TypeCombinator::removeNull(DeclaredReturnType::of($methodReflection));
    }

    /**
     * `list<Brick1|Brick2>`, from the getters the container's `$brickGetters` names. Null when that is unknown.
     */
    private static function itemsType(Type $receiverType, Scope $scope): ?Type
    {
        $containers = self::brickContainers($receiverType);
        if ($containers === null) {
            return null;
        }

        $brickTypes = [];
        foreach ($containers as $container) {
            $brickNames = self::brickNames($container);
            if ($brickNames === null) {
                return null;
            }

            foreach ($brickNames as $brickName) {
                $getterName = 'get' . ucfirst($brickName);
                if (!$container->hasMethod($getterName)) {
                    return null;
                }

                $getter = $container->getMethod($getterName, $scope);
                $brickTypes[] = TypeCombinator::removeNull(DeclaredReturnType::of($getter));
            }
        }

        if ($brickTypes === []) {
            return null;
        }

        return TypeCombinator::intersect(
            new ArrayType(new IntegerType(), TypeCombinator::union(...$brickTypes)),
            new AccessoryArrayListType(),
        );
    }

    /**
     * The default value of the generated `$brickGetters`, or null if it isn't a list of strings.
     *
     * @return list<string>|null
     */
    private static function brickNames(ClassReflection $container): ?array
    {
        $nativeReflection = $container->getNativeReflection();
        if (!$nativeReflection->hasProperty('brickGetters')) {
            return null;
        }

        $brickNames = $nativeReflection->getProperty('brickGetters')->getDefaultValue();
        if (!is_array($brickNames) || !array_is_list($brickNames)) {
            return null;
        }

        foreach ($brickNames as $brickName) {
            if (!is_string($brickName)) {
                return null;
            }
        }

        return $brickNames;
    }

    /**
     * The classes of `$type` if it is a generated brick container (a strict subclass of `Objectbrick`) or a union
     * of them; null otherwise.
     *
     * @return non-empty-list<ClassReflection>|null
     */
    private static function brickContainers(Type $type): ?array
    {
        $classReflections = $type->getObjectClassReflections();
        if ($classReflections === [] || !$type->isObject()->yes()) {
            return null;
        }

        foreach ($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf(Objectbrick::class)) {
                return null;
            }
        }

        return $classReflections;
    }
}
