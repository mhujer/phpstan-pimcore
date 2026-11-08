<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation;
use Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

/**
 * Advanced relation getters retain their declared containers and bind the metadata item's element and columns.
 */
final class MetadataReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /** @param class-string $declaringClass */
    public function __construct(
        private readonly string $declaringClass,
        private readonly ClassDefinitionReader $classDefinitionReader,
        private readonly RelationElementTypeResolver $relationElements,
        private readonly RegisteredClassOverrides $overrides,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strlen($methodReflection->getName()) > 3 && str_starts_with($methodReflection->getName(), 'get');
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $getters = GeneratedGetters::of($scope->getType($methodCall->var));
        if ($getters === null) {
            return null;
        }

        $declared = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants())->getReturnType();
        $types = [];
        foreach ($getters->definedFields($this->classDefinitionReader, $methodReflection->getName(), $scope) as $field) {
            $item = $field === null ? null : $this->itemType($field);
            if ($item === null) {
                return null;
            }

            $types[] = TypeTraverser::map($declared, static function (Type $type, callable $traverse) use ($item): Type {
                if ($type instanceof ObjectType && $type->getClassName() === $item->getClassName()) {
                    return $item;
                }

                return $traverse($type);
            });
        }

        return $this->overrides->replace(TypeCombinator::union(...$types), $scope, true);
    }

    private function itemType(FieldDefinition $field): ?GenericObjectType
    {
        if ($field->type !== AdvancedManyToManyObjectRelation::class && $field->type !== AdvancedManyToManyRelation::class) {
            return null;
        }

        $columns = $field->property('columns');
        if (!is_array($columns)) {
            return null;
        }

        $columnTypes = [];
        foreach ($columns as $column) {
            if (!is_array($column) || !isset($column['key']) || !is_string($column['key'])) {
                return null;
            }

            $columnTypes[] = new ConstantStringType($column['key']);
        }

        if ($field->type === AdvancedManyToManyObjectRelation::class) {
            $allowed = $field->property('allowedClassId');
            $element = $this->relationElements->object(is_string($allowed) ? $allowed : '');
            if ($element === null) {
                return null;
            }

            return new GenericObjectType(ObjectMetadata::class, [$element, $columnTypes === [] ? new NeverType() : TypeCombinator::union(...$columnTypes)]);
        }

        $elements = $this->relationElements->elements($field);
        if ($elements === null || $elements === []) {
            return null;
        }

        return new GenericObjectType(ElementMetadata::class, [TypeCombinator::union(...$elements), $columnTypes === [] ? new NeverType() : TypeCombinator::union(...$columnTypes)]);
    }
}
