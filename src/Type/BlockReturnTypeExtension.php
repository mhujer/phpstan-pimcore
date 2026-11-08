<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Carbon\Carbon;
use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Data\Link;
use Pimcore\Model\DataObject\Localizedfield;

/** Block items have optional child keys: persisted items can predate newly added fields. */
final class BlockReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /** @param class-string $declaringClass */
    public function __construct(
        private readonly string $declaringClass,
        private readonly ClassDefinitionReader $classDefinitionReader,
        private readonly RelationElementTypeResolver $relationElements,
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
            if ($field === null || $field->type !== Data\Block::class) {
                return null;
            }

            $item = ConstantArrayTypeBuilder::createEmpty();
            foreach ($field->children() as $child) {
                $item->setOffsetValueType(new ConstantStringType($child->name), new GenericObjectType(BlockElement::class, [$this->childType($child)]), true);
            }

            $type = DeclaredReturnType::withNullOf($declared, new ArrayType(new IntegerType(), $item->getArray()));
            if (!$declared->isSuperTypeOf($type)->yes()) {
                return null;
            }

            $types[] = $type;
        }

        return TypeCombinator::union(...$types);
    }

    private function childType(FieldDefinition $field): Type
    {
        $type = match ($field->type) {
            Data\Input::class, Data\Textarea::class, Data\Wysiwyg::class => new StringType(),
            Data\Numeric::class => $field->property('integer') === true ? new IntegerType() : new FloatType(),
            Data\Checkbox::class => new BooleanType(),
            Data\Date::class, Data\Datetime::class => new ObjectType(Carbon::class),
            Data\Select::class, Data\Multiselect::class => $this->selectType($field),
            Data\Link::class => new ObjectType(Link::class),
            Data\Image::class => new ObjectType(Asset\Image::class),
            Data\Localizedfields::class => new ObjectType(Localizedfield::class),
            Data\ManyToOneRelation::class, Data\ManyToManyRelation::class, Data\ManyToManyObjectRelation::class => $this->relationType($field),
            default => new MixedType(),
        };

        // Multiple relations generate non-null arrays.
        return in_array($field->type, [Data\ManyToManyRelation::class, Data\ManyToManyObjectRelation::class], true)
            ? $type
            : TypeCombinator::addNull($type);
    }

    private function selectType(FieldDefinition $field): Type
    {
        $options = $field->staticOptionValues();
        $type = $options === null ? new StringType() : TypeCombinator::union(...array_map(static fn (string $option): Type => new ConstantStringType($option), $options));

        // Single selects can persist an empty string, including in drafts with mandatory fields.
        if ($field->type === Data\Select::class) {
            $type = TypeCombinator::union($type, new ConstantStringType(''));
        }

        return $field->type === Data\Multiselect::class ? new ArrayType(new IntegerType(), $type) : $type;
    }

    private function relationType(FieldDefinition $field): Type
    {
        $elements = $this->relationElements->elements($field, $field->type === Data\ManyToManyObjectRelation::class, allowMissingClasses: true, allowEmptyClassNames: false);
        if ($elements === null) {
            return new MixedType();
        }

        $type = $elements === [] ? new MixedType() : TypeCombinator::union(...$elements);

        return $field->type === Data\ManyToOneRelation::class ? $type : new ArrayType(new IntegerType(), $type);
    }
}
