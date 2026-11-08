<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Listing;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\Type\RegisteredClassOverrides;
use PHPStan\PhpDoc\StubFilesExtension;
use PHPStan\Reflection\ReflectionProvider;
use RuntimeException;

/** Generates one atomic, self-contained addition to the shared stub set. */
final class ListingStubFilesExtension implements StubFilesExtension
{
    public function __construct(
        private readonly ClassDefinitionReader $definitions,
        private readonly string $tmpDir,
        private readonly RegisteredClassOverrides $overrides,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    /** @return list<string> */
    public function getFiles(): array
    {
        $definitions = $this->definitions->listingDefinitions();
        if ($definitions === null || $definitions === []) {
            return [];
        }
        foreach ($definitions as $definition) {
            if ($definition->listingParentClass !== null && $definition->listingParentClass !== '') {
                return [];
            }
        }

        $content = file_get_contents(__DIR__ . '/../../stubs/Listing.stub');
        if ($content === false) {
            throw new RuntimeException('Cannot read the Listing stub template.');
        }
        $skeletons = [];
        $overrides = $this->overrides->registered() ?? [];
        foreach ($definitions as $definition) {
            $class = ClassDefinitionReader::GENERATED_NAMESPACE . ucfirst($definition->name ?? '');
            $skeletons[$class] = 'Pimcore\\Model\\DataObject\\Concrete';
            $element = $class;
            $override = $overrides[$class] ?? null;
            if ($override !== null && $this->reflectionProvider->hasClass($override)) {
                // Native ancestry does not resolve PHPDocs or re-enter stub initialization. Never ask the
                // ClassReflection for template types or parents here.
                $native = $this->reflectionProvider->getClass($override)->getNativeReflection();
                if ($native->isSubclassOf($class)) {
                    $element = $native->getName();
                    while ($native->getName() !== $class) {
                        $parent = $native->getParentClass();
                        if ($parent === false) {
                            break;
                        }
                        $skeletons[$native->getName()] = $parent->getName();
                        $native = $parent;
                    }
                }
            }
            $content .= "\nnamespace " . $class . " {\n/** @extends \\Pimcore\\Model\\DataObject\\Listing\\Concrete<\\" . $element . "> */\nclass Listing extends \\Pimcore\\Model\\DataObject\\Listing\\Concrete {}\n}\n";
        }
        foreach ($skeletons as $class => $parent) {
            $separator = strrpos($class, '\\');
            if ($separator === false) {
                $namespace = '';
                $shortName = $class;
            } else {
                $namespace = substr($class, 0, $separator);
                $shortName = substr($class, $separator + 1);
            }
            $content .= "namespace " . $namespace . " { class " . $shortName . " extends \\" . $parent . " {} }\n";
        }
        $directory = $this->tmpDir . '/pimcore-stubs';
        if (!is_dir($directory) && !mkdir($directory, 0o777, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create the Listing stub directory.');
        }
        $file = $directory . '/listings-' . md5($this->definitions->directory()) . '.stub';
        if (!is_file($file) || file_get_contents($file) !== $content) {
            $temporary = tempnam($directory, 'listings-');
            if ($temporary === false || file_put_contents($temporary, $content) === false || !rename($temporary, $file)) {
                throw new RuntimeException('Cannot write the Listing stub.');
            }
        }

        return [$file];
    }
}
