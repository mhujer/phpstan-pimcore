<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ClassDefinition\FieldDefinition;
use PhpParser\Node;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Pimcore\Model\DataObject\ClassDefinition\CalculatorClassInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\CalculatedValue;
use Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections;
use Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks;

/** A calculator may narrow its object PHPDoc to all the generated classes which use it. */
final readonly class CalculatorIgnoreErrorExtension implements IgnoreErrorExtension
{
    public function __construct(private ClassDefinitionReader $definitions)
    {
    }

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        unset($node);
        $class = $scope->getClassReflection();
        $method = $scope->getFunction();
        if ($error->getIdentifier() !== 'method.childParameterType' || $class === null || $method === null
            || strcasecmp($method->getName(), 'compute') !== 0
            || !$class->implementsInterface(CalculatorClassInterface::class)
        ) {
            return false;
        }

        $parameter = $method->getOnlyVariant()->getParameters()[0] ?? null;
        // The same identifier also reports context parameter errors. Never silence those.
        if ($parameter === null || !str_starts_with($error->getMessage(), 'Parameter #1 $' . $parameter->getName() . ' (')) {
            return false;
        }

        if ($scope instanceof DependencyTracker) {
            $scope->trackDirectoryDependency($this->definitions->directory(), 'definition_*.php');
            foreach (glob($this->definitions->directory() . '/definition_*.php') ?: [] as $file) {
                $scope->trackFileDependency($file);
            }
        }
        $definitions = $this->definitions->listingDefinitions();
        if ($definitions === null) {
            return false;
        }

        $found = false;
        foreach ($definitions as $definition) {
            $usesCalculator = $this->usesCalculator($definition->fields(), $class->getName(), $scope, []);
            if ($usesCalculator === null) {
                return false;
            }
            if (!$usesCalculator) {
                continue;
            }
            $found = true;
            $owner = new ObjectType(ClassDefinitionReader::GENERATED_NAMESPACE . ucfirst((string) $definition->name));
            if (!$parameter->getType()->isSuperTypeOf($owner)->yes()) {
                return false;
            }
        }

        return $found;
    }

    /**
     * Null means the owner graph is incomplete, so narrowing cannot be proved safe.
     * @param array<string, FieldDefinition> $fields
     * @param array<string, true> $visited
     */
    private function usesCalculator(array $fields, string $calculatorClass, Scope $scope, array $visited): ?bool
    {
        $found = false;
        foreach ($fields as $field) {
            $calculator = $field->property('calculatorClass');
            if ($field->type === CalculatedValue::class && $field->property('calculatorType') === 'class'
                && is_string($calculator) && strcasecmp(ltrim($calculator, '\\'), $calculatorClass) === 0
            ) {
                $found = true;
            }
            $children = $this->usesCalculator($field->children(), $calculatorClass, $scope, $visited);
            if ($children === null) {
                return null;
            }
            $found = $found || $children;
            $namespace = match ($field->type) {
                Objectbricks::class => 'Objectbrick\\Data\\',
                Fieldcollections::class => 'Fieldcollection\\Data\\',
                default => null,
            };
            if ($namespace === null) {
                continue;
            }
            $allowed = $field->property('allowedTypes');
            if (!is_array($allowed)) {
                return null;
            }
            foreach ($allowed as $name) {
                if (!is_string($name)) {
                    return null;
                }
                $class = ClassDefinitionReader::GENERATED_NAMESPACE . $namespace . ucfirst($name);
                if (isset($visited[$class])) {
                    continue;
                }
                $definition = $this->definitions->forGeneratedClass($class, $scope);
                if ($definition === null) {
                    return null;
                }
                $nested = $this->usesCalculator($definition->fields(), $calculatorClass, $scope, [...$visited, $class => true]);
                if ($nested === null) {
                    return null;
                }
                $found = $found || $nested;
            }
        }

        return $found;
    }
}
