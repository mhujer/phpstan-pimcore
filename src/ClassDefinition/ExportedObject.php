<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ClassDefinition;

/**
 * An object as `var_export()` writes it, `\Class::__set_state(array(...))`, read from the AST.
 *
 * @internal
 */
final readonly class ExportedObject
{
    /**
     * @param array<string, mixed> $properties only the properties whose values are constant expressions
     */
    public function __construct(
        public string $class,
        public array $properties,
    ) {
    }
}
