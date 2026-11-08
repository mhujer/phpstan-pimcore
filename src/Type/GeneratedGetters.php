<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Expr\TypeExpr;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData as FieldcollectionItem;
use Pimcore\Model\DataObject\Objectbrick;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData as BrickItem;

/**
 * An exactly known generated class (or a union of them) and the getters of its fields.
 *
 * Each receiver class is paired with its generated class. Calls resolve on the receiver, so getters a subclass adds
 * count. Whether a getter belongs to a field, and what the class definition says about it, is decided on the
 * generated class only.
 */
final readonly class GeneratedGetters
{
    private const string FIELD_CONSTANT_PREFIX = 'FIELD_';

    private const array GENERATED_PARENTS = [Concrete::class, Objectbrick::class, BrickItem::class, FieldcollectionItem::class];

    /**
     * @param non-empty-list<ClassReflection> $classReflections the receiver classes
     * @param non-empty-list<ClassReflection|null> $generatedClasses the generated class of each receiver class, if it has one
     */
    private function __construct(
        private Type $type,
        private array $classReflections,
        private array $generatedClasses,
    ) {
    }

    /**
     * Null unless every class of `$type` is generated: a strict subclass of `Concrete`, of `Objectbrick` (a brick
     * container) or of a brick or fieldcollection item base. The bases themselves don't count.
     */
    public static function of(Type $type): ?self
    {
        $classReflections = $type->getObjectClassReflections();
        if ($classReflections === []) {
            return null;
        }

        $generatedClasses = [];
        foreach ($classReflections as $classReflection) {
            if (!self::isGenerated($classReflection)) {
                return null;
            }

            $generatedClasses[] = self::generatedClass($classReflection);
        }

        return new self($type, $classReflections, $generatedClasses);
    }

    public function type(): Type
    {
        return $this->type;
    }

    /**
     * The class definition's field behind `$getterName`, one per receiver class. Null where the generated class
     * doesn't declare the getter itself (a method inherited from Pimcore isn't a field) or the definition doesn't
     * know it.
     *
     * @return non-empty-list<FieldDefinition|null>
     */
    public function definedFields(ClassDefinitionReader $classDefinitionReader, string $getterName, Scope $scope): array
    {
        $fields = [];
        foreach ($this->generatedClasses as $generatedClass) {
            $fields[] = $generatedClass !== null && self::declaresGetter($generatedClass, $getterName)
                ? $classDefinitionReader->forGeneratedClass($generatedClass->getName(), $scope)?->fieldForGetter($getterName)
                : null;
        }

        return $fields;
    }

    public function hasGetter(string $fieldName): bool
    {
        foreach ($this->classReflections as $classReflection) {
            // Pimcore calls `get<Name>` dynamically; PHP method lookup is case-insensitive.
            if (!$classReflection->hasMethod(self::getterName($fieldName))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether every class declares the property `$fieldName`, inherited ones included, with this exact case.
     */
    public function hasProperty(string $fieldName): bool
    {
        foreach ($this->classReflections as $classReflection) {
            if (!$classReflection->hasNativeProperty($fieldName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * A localized field lives in `localizedfields`, not in a property of its own, and its getter takes the language.
     */
    public function isLocalizedField(string $fieldName, Scope $scope): bool
    {
        if (!$this->hasGetter($fieldName)) {
            return false;
        }

        foreach ($this->classReflections as $classReflection) {
            if ($classReflection->hasNativeProperty($fieldName)) {
                return false;
            }

            $getter = $classReflection->getMethod(self::getterName($fieldName), $scope);
            $firstParameter = $getter->getOnlyVariant()->getParameters()[0] ?? null;
            if ($firstParameter?->getName() !== 'language') {
                return false;
            }
        }

        return true;
    }

    /** Localized field lookups use the exact field name, including calculated values. */
    public function isExactLocalizedField(string $fieldName, Scope $scope): bool
    {
        if (!$this->isLocalizedField($fieldName, $scope)) {
            return false;
        }

        foreach ($this->generatedClasses as $generatedClass) {
            if ($generatedClass === null || !self::hasFieldNameConstant($generatedClass, $fieldName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pimcore generates no property for a calculated value, only a getter, and `getValueForFieldName()` computes it
     * when the class definition has a calculated value of exactly that name. Without the definition, the generated
     * class tells: it declares the getter itself, takes no language, and has a field name constant (`FIELD_*`, which
     * Pimcore generates for every field) whose value is the name with this exact case. A Pimcore getter such as
     * `getFullPath()` has neither.
     */
    public function isCalculatedValue(string $fieldName, Scope $scope): bool
    {
        if ($this->isLocalizedField($fieldName, $scope)) {
            return false;
        }

        foreach ($this->classReflections as $classReflection) {
            if ($classReflection->hasNativeProperty($fieldName)) {
                return false;
            }
        }

        foreach ($this->generatedClasses as $generatedClass) {
            if ($generatedClass === null
                || !self::declaresGetter($generatedClass, self::getterName($fieldName))
                || !self::hasFieldNameConstant($generatedClass, $fieldName)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the generated class itself declares `$getterName`, with this exact case, rather than inheriting it from
     * Pimcore. Only such getters belong to a field.
     */
    private static function declaresGetter(ClassReflection $classReflection, string $getterName): bool
    {
        if (!$classReflection->hasNativeMethod($getterName)) {
            return false;
        }

        $getter = $classReflection->getNativeMethod($getterName);

        return $getter->getDeclaringClass()->getName() === $classReflection->getName() && $getter->getName() === $getterName;
    }

    /**
     * The getter's name as declared, e.g. `getTitle` for `title`. Only valid after `hasGetter()` returned true.
     */
    public function declaredGetterName(string $fieldName, Scope $scope): string
    {
        return $this->classReflections[0]->getMethod(self::getterName($fieldName), $scope)->getName();
    }

    /**
     * What `get<Name>(...$args)` returns. Only valid after `hasGetter()` returned true.
     *
     * @param array<Arg> $args
     */
    public function getterReturnType(string $fieldName, array $args, Scope $scope): Type
    {
        // Resolving a call through the scope lets other extensions narrow the getter, too.
        return $scope->getType(new MethodCall(new TypeExpr($this->type), new Identifier(self::getterName($fieldName)), $args));
    }

    private static function getterName(string $fieldName): string
    {
        return 'get' . ucfirst($fieldName);
    }

    private static function hasFieldNameConstant(ClassReflection $classReflection, string $fieldName): bool
    {
        foreach ($classReflection->getNativeReflection()->getReflectionConstants() as $constant) {
            if (str_starts_with($constant->getName(), self::FIELD_CONSTANT_PREFIX) && $constant->getValue() === $fieldName) {
                return true;
            }
        }

        return false;
    }

    /**
     * The class Pimcore generated for `$classReflection`: the class itself or, for a class override (a project class
     * Pimcore instantiates in place of a generated class), its nearest ancestor in Pimcore's generated namespace.
     */
    private static function generatedClass(ClassReflection $classReflection): ?ClassReflection
    {
        for ($class = $classReflection; $class !== null; $class = $class->getParentClass()) {
            if (str_starts_with($class->getName(), ClassDefinitionReader::GENERATED_NAMESPACE) && self::isGenerated($class)) {
                return $class;
            }
        }

        return null;
    }

    private static function isGenerated(ClassReflection $classReflection): bool
    {
        foreach (self::GENERATED_PARENTS as $parent) {
            if ($classReflection->isSubclassOf($parent)) {
                return true;
            }
        }

        return false;
    }
}
