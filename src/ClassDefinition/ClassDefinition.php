<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ClassDefinition;

/**
 * A parsed class definition file of a DataObject class, an objectbrick or a fieldcollection.
 */
final readonly class ClassDefinition
{
    /**
     * @param array<string, FieldDefinition> $fields
     */
    public function __construct(
        public string $file,
        private array $fields,
        public ?string $name = null,
        public ?string $listingParentClass = null,
    ) {
    }

    /**
     * A field by its exact name, wherever it sits in the layout, including inside `localizedfields`.
     */
    public function field(string $name): ?FieldDefinition
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * The field whose getter `$methodName` is (`get` + the field name with ucfirst()), matched case-insensitively
     * like PHP method names.
     */
    public function fieldForGetter(string $methodName): ?FieldDefinition
    {
        foreach ($this->fields as $field) {
            if (strcasecmp('get' . ucfirst($field->name), $methodName) === 0) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return array<string, FieldDefinition>
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
