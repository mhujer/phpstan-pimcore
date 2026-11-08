<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use Pimcore\Model\DataObject\Concrete;

/** The registered factory replacements, shared by inference and registration diagnostics. */
final class RegisteredClassOverrides
{
    public function __construct(
        private readonly ContainerXmlReader $reader,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function configured(Scope $scope): bool
    {
        return is_array($this->entries($scope));
    }

    public function entry(string $class, Scope $scope): ?string
    {
        return $this->entries($scope)[$class] ?? null;
    }

    /**
     * The registered overrides without a leading backslash on either side, as Pimcore normalises them at runtime.
     * Null when the Container XML holds no override map.
     *
     * It records no result cache dependency, so its caller must track the Container XML itself, as ListingStubDependenciesRule
     * does for the Listing stub. Other rules and type extensions use entry(), isValid() and the other methods, which track it.
     *
     * @return array<string, string>|null
     */
    public function registered(): ?array
    {
        $entries = $this->reader->config(['models', 'class_overrides']);
        if (!is_array($entries)) {
            return null;
        }
        $registered = [];
        foreach ($entries as $class => $override) {
            if (!is_string($class) || !is_string($override)) {
                continue;
            }

            $registered[ltrim($class, '\\')] = ltrim($override, '\\');
        }

        return $registered;
    }

    public function isValid(string $class, Scope $scope): bool
    {
        $override = $this->entry($class, $scope);

        return $override !== null && $this->reflectionProvider->hasClass($override)
            && $this->reflectionProvider->getClass($override)->isSubclassOf($class);
    }

    public function replacement(string $class, Scope $scope): ?ObjectType
    {
        $entry = $this->entry($class, $scope);

        return $entry !== null && $this->isValid($class, $scope) ? new ObjectType($entry) : null;
    }

    /** @return non-empty-array<non-empty-string, ObjectType>|null */
    public function subtypeMap(string $parameter, Scope $scope): ?array
    {
        if ($scope instanceof DependencyTracker) {
            $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('parameter', $parameter));
        }
        $entries = $this->reader->parameter($parameter);
        if (!is_array($entries) || $entries === []) {
            return null;
        }
        $map = [];
        foreach ($entries as $subtype => $class) {
            if (!is_string($subtype) || $subtype === '' || !is_string($class) || $class === '') {
                return null;
            }
            $class = ltrim($class, '\\');
            $map[$subtype] = $this->replacement($class, $scope) ?? new ObjectType($class);
        }

        return $map;
    }

    public function generatedClass(ClassReflection $class): ?ClassReflection
    {
        for ($current = $class; $current !== null; $current = $current->getParentClass()) {
            if (str_starts_with($current->getName(), 'Pimcore\\Model\\DataObject\\') && $current->isSubclassOf(Concrete::class)) {
                return $current;
            }
        }

        return null;
    }

    /** Null means this class is outside DataObject factory typing; NullType means typeMatch rejects it. */
    public function loadedClass(ClassReflection $class, Scope $scope): ?Type
    {
        $generated = $this->generatedClass($class);
        if ($generated === null) {
            return null;
        }
        $replacement = $this->replacement($generated->getName(), $scope) ?? new ObjectType($generated->getName());
        $actual = $replacement->getClassName();

        return $class->getName() === $actual || $this->reflectionProvider->getClass($actual)->isSubclassOf($class->getName())
            ? $replacement : new NullType();
    }

    /** Replace only exact generated element classes, retaining arrays, nullability and metadata containers. */
    public function replace(Type $type, Scope $scope, bool $objectsOnly = false): Type
    {
        return TypeTraverser::map($type, function (Type $inner, callable $traverse) use ($scope, $objectsOnly): Type {
            if ($inner instanceof ObjectType && (!$objectsOnly || str_starts_with($inner->getClassName(), 'Pimcore\\Model\\DataObject\\'))) {
                $replacement = $this->replacement($inner->getClassName(), $scope);
                if ($replacement !== null) {
                    return $replacement;
                }
            }

            return $traverse($inner);
        });
    }

    /** @return array<string, string>|null */
    private function entries(Scope $scope): ?array
    {
        if ($scope instanceof DependencyTracker) {
            $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('parameter', 'pimcore.config', 'models', 'class_overrides'));
        }

        return $this->registered();
    }
}
