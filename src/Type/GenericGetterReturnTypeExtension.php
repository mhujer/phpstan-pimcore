<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\Localizedfield;

/**
 * Methods that take a field name return what that field's getter returns:
 *
 * - `get('sku')` calls `getSku()` (DataObjects, brick containers, brick items, fieldcollection items);
 * - `getValueForFieldName('sku')` reads the property `$sku`; a localized field has none, so it's always null; a
 *   calculated value has none either, but is computed as its getter does;
 * - `getLocalizedValue('title')` reads a localized field of the owner that `getLocalizedfields()` binds.
 *
 * Registered once per Pimcore class that declares one of these methods.
 */
final class GenericGetterReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const array METHODS = ['get', 'getValueForFieldName', 'getLocalizedfields', 'getLocalizedValue'];

    /**
     * @param class-string $declaringClass
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
        return in_array($methodReflection->getName(), self::METHODS, true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $receiverType = $scope->getType($methodCall->var);
        $args = $methodCall->getArgs();

        if ($methodReflection->getName() === 'getLocalizedfields') {
            return self::bindOwner($receiverType, $methodReflection, $args, $scope);
        }

        if ($methodReflection->getName() === 'getLocalizedValue') {
            $receiverType = $receiverType->getTemplateType(Localizedfield::class, 'TOwner');
        }

        $getters = GeneratedGetters::of($receiverType);
        if ($getters === null || !isset($args[0]) || $args[0]->unpack) {
            return null;
        }

        $fieldNames = $scope->getType($args[0]->value)->getConstantStrings();
        if ($fieldNames === []) {
            return null;
        }

        $types = [];
        foreach ($fieldNames as $fieldName) {
            $type = self::fieldType($methodReflection->getName(), $getters, $fieldName->getValue(), $args, $scope);
            if ($type === null) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    /**
     * @param array<Arg> $args
     */
    private static function fieldType(string $method, GeneratedGetters $getters, string $fieldName, array $args, Scope $scope): ?Type
    {
        // The language, where the method takes one, is passed on to the getter.
        $languageArgs = array_slice($args, 1, 1);

        return match ($method) {
            'get' => $getters->hasGetter($fieldName)
                ? $getters->getterReturnType($fieldName, $languageArgs, $scope)
                : null,
            'getValueForFieldName' => match (true) {
                $getters->isLocalizedField($fieldName, $scope) => new NullType(),
                $getters->hasProperty($fieldName) && $getters->hasGetter($fieldName),
                $getters->isCalculatedValue($fieldName, $scope) => $getters->getterReturnType($fieldName, [], $scope),
                default => null,
            },
            // Anything but a localized field throws.
            'getLocalizedValue' => $getters->isLocalizedField($fieldName, $scope)
                ? $getters->getterReturnType($fieldName, $languageArgs, $scope)
                : null,
            default => null,
        };
    }

    /**
     * `getLocalizedfields()` returns `Localizedfield<Owner>`, so `getLocalizedValue()` knows whose fields it reads.
     *
     * @param array<Arg> $args
     */
    private static function bindOwner(Type $receiverType, MethodReflection $methodReflection, array $args, Scope $scope): ?Type
    {
        $owner = GeneratedGetters::of($receiverType);
        if ($owner === null) {
            return null;
        }

        $declaredType = ParametersAcceptorSelector::selectFromArgs($scope, $args, $methodReflection->getVariants())
            ->getReturnType();

        return DeclaredReturnType::withNullOf($declaredType, new GenericObjectType(Localizedfield::class, [$owner->type()]));
    }
}
