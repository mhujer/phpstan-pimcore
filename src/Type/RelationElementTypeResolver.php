<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;

/** Resolves relation elements independently of their array or metadata containers. */
final class RelationElementTypeResolver
{
    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function object(string $name): ?ObjectType
    {
        if ($name === '') {
            return new ObjectType(Concrete::class);
        }

        $class = ClassDefinitionReader::GENERATED_NAMESPACE . $name;
        if (!$this->reflectionProvider->hasClass($class) || !$this->reflectionProvider->getClass($class)->isSubclassOf(Concrete::class)) {
            return null;
        }

        return new ObjectType($class);
    }

    /**
     * Blocks accept absent class lists as unrestricted, but reject empty entries. Metadata requires an explicit list
     * and treats an empty class name as unrestricted. Null means unresolved; an empty list means no allowed kinds.
     *
     * @return list<ObjectType>|null
     */
    public function elements(FieldDefinition $field, bool $objectsOnly = false, bool $allowMissingClasses = false, bool $allowEmptyClassNames = true): ?array
    {
        $elements = [];
        if ($objectsOnly || $field->property('objectsAllowed') === true) {
            $classes = $field->property('classes');
            if (!is_array($classes)) {
                if (!$allowMissingClasses) {
                    return null;
                }

                $classes = [];
            }

            if ($classes === []) {
                $elements[] = new ObjectType(Concrete::class);
            }

            foreach ($classes as $class) {
                $name = is_array($class) ? ($class['classes'] ?? null) : null;
                $element = is_string($name) && ($allowEmptyClassNames || $name !== '') ? $this->object($name) : null;
                if ($element === null) {
                    return null;
                }

                $elements[] = $element;
            }
        }

        if (!$objectsOnly) {
            foreach (['assetsAllowed' => Asset::class, 'documentsAllowed' => Document::class] as $property => $class) {
                if ($field->property($property) !== true) {
                    continue;
                }

                $elements[] = new ObjectType($class);
            }
        }

        return $elements;
    }
}
