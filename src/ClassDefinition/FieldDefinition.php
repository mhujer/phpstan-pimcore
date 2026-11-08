<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ClassDefinition;

use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

/**
 * One field of a class definition, e.g. a select with its options.
 */
final readonly class FieldDefinition
{
    /**
     * @param string $type the field's data class, e.g. `Pimcore\Model\DataObject\ClassDefinition\Data\Select`
     * @param bool $localized whether the field lives inside the `localizedfields` field
     * @param array<string, mixed> $properties
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $localized,
        private array $properties,
    ) {
    }

    /**
     * The value of a property of the field's data class, as exported to the definition file: a scalar, null, or an
     * array of them. Null if the property is missing or its value isn't a constant expression. Nested data objects
     * (`__set_state()` calls) come back as `ExportedObject`.
     */
    public function property(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * Direct children of a block or localizedfields container. Child names stay in this container rather than
     * leaking into the owning class, and each child retains its own configuration and nested children.
     *
     * @return array<string, self>
     */
    public function children(): array
    {
        $children = $this->property('children');
        if (!is_array($children)) {
            return [];
        }

        $fields = [];
        foreach ($children as $child) {
            if (!$child instanceof ExportedObject) {
                continue;
            }

            $name = $child->properties['name'] ?? null;
            if (!is_string($name) || isset($fields[$name])) {
                continue;
            }

            $fields[$name] = new self($name, $child->class, $this->localized || $this->type === Localizedfields::class, $child->properties);
        }

        return $fields;
    }

    /**
     * Static options shared by select getters, select writes and block child typing. Exact field types only:
     * subclasses such as Country have their own runtime options.
     *
     * @return non-empty-list<string>|null
     */
    public function staticOptionValues(): ?array
    {
        if ($this->type !== Select::class && $this->type !== Multiselect::class) {
            return null;
        }

        // Mirrors Pimcore's OptionsProviderTrait::useConfiguredOptions().
        $providerType = $this->property('optionsProviderType');
        $providerClass = $this->property('optionsProviderClass');
        if ($providerType !== 'configure' && !($providerType === null && ($providerClass === null || $providerClass === ''))) {
            return null;
        }

        $options = $this->property('options');
        if (!is_array($options) || $options === []) {
            return null;
        }

        $values = [];
        foreach ($options as $option) {
            $value = is_array($option) ? $option['value'] ?? null : null;
            if (!is_string($value) && !is_int($value)) {
                return null;
            }

            // Pimcore stores the value in a string column.
            $values[] = (string) $value;
        }

        return $values;
    }
}
