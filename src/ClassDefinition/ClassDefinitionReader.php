<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ClassDefinition;

use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Parser\Parser;
use PHPStan\Parser\ParserErrorsException;

/**
 * Reads the class definitions in `classDefinitionDirectory` by parsing their AST. The files are never executed,
 * so analysis never boots the Pimcore kernel or runs project code.
 *
 * The generated class is the primary source: callers use a definition only to narrow what the generated class
 * already declares.
 */
final class ClassDefinitionReader
{
    /**
     * Pimcore writes every generated class into this namespace or below it.
     */
    public const string GENERATED_NAMESPACE = 'Pimcore\\Model\\DataObject\\';

    /**
     * Definition file name patterns, relative to the directory, by the namespace of the generated class.
     */
    private const array FILES_BY_NAMESPACE = [
        self::GENERATED_NAMESPACE . 'Objectbrick\\Data\\' => 'objectbricks/%s.php',
        self::GENERATED_NAMESPACE . 'Fieldcollection\\Data\\' => 'fieldcollections/%s.php',
        self::GENERATED_NAMESPACE => 'definition_%s.php',
    ];

    private const string LAYOUT_NAMESPACE = 'Pimcore\\Model\\DataObject\\ClassDefinition\\Layout\\';

    private const string LOCALIZEDFIELDS = 'Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Localizedfields';

    private readonly string $directory;

    /**
     * Lower-cased relative path => actual relative path, of every definition file in the directory.
     *
     * @var array<string, string>|null
     */
    private ?array $files = null;

    /**
     * @var array<string, ClassDefinition|null>
     */
    private array $definitions = [];

    public function __construct(
        ?string $classDefinitionDirectory,
        string $currentWorkingDirectory,
        private readonly Parser $parser,
    ) {
        $this->directory = ClassDefinitionDirectory::resolve($classDefinitionDirectory, $currentWorkingDirectory);
    }

    /**
     * The definition of a generated class: a DataObject class, an objectbrick or a fieldcollection item. Null if
     * there is none, or it can't be read.
     *
     * The file is tracked as a dependency of the analysed file, so that editing, adding or removing it invalidates
     * PHPStan's result cache for that file.
     */
    public function forGeneratedClass(string $className, Scope $scope): ?ClassDefinition
    {
        $relativePath = self::relativePath(ltrim($className, '\\'));
        if ($relativePath === null) {
            return null;
        }

        // Pimcore names the file after the definition, the generated class is that name with ucfirst().
        $relativePath = $this->files()[strtolower($relativePath)] ?? $relativePath;
        $file = $this->directory . '/' . $relativePath;

        if ($scope instanceof DependencyTracker) {
            $scope->trackFileDependency($file);
        }

        if (!array_key_exists($file, $this->definitions)) {
            $this->definitions[$file] = is_file($file) ? $this->read($file) : null;
        }

        return $this->definitions[$file];
    }

    /**
     * All DataObject class definitions, without consulting PHPStan reflection during stub initialization.
     * An unreadable definition prevents emitting a partial generic Listing hierarchy.
     * @return list<ClassDefinition>|null
     */
    public function listingDefinitions(): ?array
    {
        $definitions = [];
        foreach (glob($this->directory . '/definition_*.php') ?: [] as $file) {
            $definition = $this->read($file);
            if ($definition === null || $definition->name === null) {
                return null;
            }
            $definitions[] = $definition;
        }

        return $definitions;
    }

    public function directory(): string
    {
        return $this->directory;
    }

    private static function relativePath(string $className): ?string
    {
        foreach (self::FILES_BY_NAMESPACE as $namespace => $pattern) {
            if (!str_starts_with($className, $namespace)) {
                continue;
            }

            $shortName = substr($className, strlen($namespace));
            if ($shortName === '' || str_contains($shortName, '\\')) {
                return null;
            }

            return sprintf($pattern, $shortName);
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function files(): array
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $this->files = [];
        foreach (array_unique(self::FILES_BY_NAMESPACE) as $pattern) {
            foreach (glob($this->directory . '/' . sprintf($pattern, '*')) ?: [] as $file) {
                $relativePath = substr($file, strlen($this->directory) + 1);
                $this->files[strtolower($relativePath)] = $relativePath;
            }
        }

        return $this->files;
    }

    private function read(string $file): ?ClassDefinition
    {
        try {
            $statements = $this->parser->parseFile($file);
        } catch (ParserErrorsException) {
            return null;
        }

        foreach ($statements as $statement) {
            if (!$statement instanceof Return_ || $statement->expr === null) {
                continue;
            }

            $root = self::evaluate($statement->expr);
            if (!$root instanceof ExportedObject) {
                return null;
            }

            $fields = [];
            $layout = $root->properties['layoutDefinitions'] ?? null;
            if ($layout instanceof ExportedObject) {
                self::collectFields($layout, false, $fields);
            }

            return new ClassDefinition(
                $file,
                $fields,
                is_string($root->properties['name'] ?? null) ? $root->properties['name'] : null,
                is_string($root->properties['listingParentClass'] ?? null) ? $root->properties['listingParentClass'] : null,
            );
        }

        return null;
    }

    /**
     * @param array<string, FieldDefinition> $fields
     */
    private static function collectFields(ExportedObject $container, bool $localized, array &$fields): void
    {
        $children = $container->properties['children'] ?? null;
        if (!is_array($children)) {
            return;
        }

        foreach ($children as $child) {
            if (!$child instanceof ExportedObject) {
                continue;
            }

            if (str_starts_with($child->class, self::LAYOUT_NAMESPACE)) {
                self::collectFields($child, $localized, $fields);
                continue;
            }

            $name = $child->properties['name'] ?? null;
            if (is_string($name) && !isset($fields[$name])) {
                $fields[$name] = new FieldDefinition($name, $child->class, $localized, $child->properties);
            }

            if ($child->class !== self::LOCALIZEDFIELDS) {
                continue;
            }

            self::collectFields($child, true, $fields);
        }
    }

    /**
     * The value of a constant expression as `var_export()` writes it.
     *
     * @throws UnsupportedExpression
     */
    private static function evaluate(Expr $expr): mixed
    {
        return match (true) {
            $expr instanceof Scalar\String_, $expr instanceof Scalar\Int_, $expr instanceof Scalar\Float_ => $expr->value,
            $expr instanceof Expr\UnaryMinus && ($expr->expr instanceof Scalar\Int_ || $expr->expr instanceof Scalar\Float_) => -$expr->expr->value,
            $expr instanceof Expr\ConstFetch => match (strtolower($expr->name->toString())) {
                'null' => null,
                'true' => true,
                'false' => false,
                default => throw new UnsupportedExpression(),
            },
            $expr instanceof Expr\Array_ => self::evaluateArray($expr),
            $expr instanceof Expr\StaticCall => self::evaluateSetState($expr),
            default => throw new UnsupportedExpression(),
        };
    }

    /**
     * @return array<mixed>
     * @throws UnsupportedExpression
     */
    private static function evaluateArray(Expr\Array_ $array): array
    {
        $values = [];
        foreach ($array->items as $item) {
            if ($item->byRef || $item->unpack) {
                throw new UnsupportedExpression();
            }

            $value = self::evaluate($item->value);
            if ($item->key === null) {
                $values[] = $value;
                continue;
            }

            $key = self::evaluate($item->key);
            if (!is_int($key) && !is_string($key)) {
                throw new UnsupportedExpression();
            }

            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * `\Class::__set_state(array(...))`. A property whose value isn't a constant expression is left out, so one odd
     * property (a custom data type, an enum) doesn't cost the whole definition.
     *
     * @throws UnsupportedExpression
     */
    private static function evaluateSetState(Expr\StaticCall $call): ExportedObject
    {
        $state = $call->getArgs()[0]->value ?? null;
        if (
            !$call->class instanceof Name
            || !$call->name instanceof Identifier
            || strtolower($call->name->toString()) !== '__set_state'
            || !$state instanceof Expr\Array_
        ) {
            throw new UnsupportedExpression();
        }

        $properties = [];
        foreach ($state->items as $item) {
            if (!$item->key instanceof Scalar\String_) {
                continue;
            }

            try {
                $properties[$item->key->value] = self::evaluate($item->value);
            } catch (UnsupportedExpression) {
            }
        }

        return new ExportedObject($call->class->toString(), $properties);
    }
}
