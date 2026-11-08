<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData as FieldcollectionItem;

/**
 * The fieldcollection getter returns `Fieldcollection<Item1|Item2>|null`.
 *
 * Pimcore 2026.x generates that type itself. Pimcore 12.3 declares a bare `Fieldcollection`, so the item types come
 * from the field's `allowedTypes` in the class definition, or are any fieldcollection item without one. A getter that
 * already names its item types is left alone.
 *
 * @todo Remove this extension, and its registration, when support for Pimcore 12.3 is dropped.
 */
final class FieldcollectionReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    private const string ITEM_NAMESPACE = 'Pimcore\\Model\\DataObject\\Fieldcollection\\Data\\';

    public function __construct(
        private readonly ClassDefinitionReader $classDefinitionReader,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getClass(): string
    {
        return Concrete::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return self::isBareFieldcollection(TypeCombinator::removeNull(DeclaredReturnType::of($methodReflection)));
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $getters = GeneratedGetters::of($scope->getType($methodCall->var));
        if ($getters === null) {
            return null;
        }

        $itemTypes = [];
        foreach ($getters->definedFields($this->classDefinitionReader, $methodReflection->getName(), $scope) as $field) {
            $itemTypes[] = $this->itemType($field);
        }

        return DeclaredReturnType::withNullOf(
            DeclaredReturnType::of($methodReflection),
            new GenericObjectType(Fieldcollection::class, [TypeCombinator::union(...$itemTypes)]),
        );
    }

    /**
     * The items the class definition allows for the getter's field; any fieldcollection item if that is unknown.
     */
    private function itemType(?FieldDefinition $field): Type
    {
        $anyItem = new ObjectType(FieldcollectionItem::class);

        if ($field === null || $field->type !== Fieldcollections::class) {
            return $anyItem;
        }

        $allowedTypes = $field->property('allowedTypes');
        if (!is_array($allowedTypes) || $allowedTypes === []) {
            // No restriction in the definition: any fieldcollection.
            return $anyItem;
        }

        $itemTypes = [];
        foreach ($allowedTypes as $allowedType) {
            if (!is_string($allowedType) || $allowedType === '') {
                return $anyItem;
            }

            // Pimcore's generated item class, as its class builder names it.
            $itemClass = self::ITEM_NAMESPACE . ucfirst($allowedType);
            if (!$this->reflectionProvider->hasClass($itemClass)
                || !$this->reflectionProvider->getClass($itemClass)->isSubclassOf(FieldcollectionItem::class)
            ) {
                // A stale definition naming an item that isn't generated.
                return $anyItem;
            }

            $itemTypes[] = new ObjectType($itemClass);
        }

        return TypeCombinator::union(...$itemTypes);
    }

    /**
     * `Fieldcollection` without a type argument, as Pimcore 12.3 declares the getter.
     */
    private static function isBareFieldcollection(Type $type): bool
    {
        if ($type->getObjectClassNames() !== [Fieldcollection::class]) {
            return false;
        }

        $itemType = $type->getTemplateType(Fieldcollection::class, 'TItem');

        return (new ObjectType(FieldcollectionItem::class))->equals($itemType);
    }
}
