<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Listing;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\Type\RegisteredClassOverrides;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;

/**
 * Every analysed file may iterate a Listing, including files with no method calls of their own.
 * @implements Rule<FileNode>
 */
final class ListingStubDependenciesRule implements Rule
{
    public function __construct(
        private readonly ClassDefinitionReader $definitions,
        private readonly RegisteredClassOverrides $overrides,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        unset($node);

        $scope->trackDirectoryDependency($this->definitions->directory(), 'definition_*.php');
        foreach (glob($this->definitions->directory() . '/definition_*.php') ?: [] as $file) {
            $scope->trackFileDependency($file);
        }
        $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('parameter', 'pimcore.config', 'models', 'class_overrides'));

        foreach ($this->overrides->registered() ?? [] as $override) {
            if (!$this->reflectionProvider->hasClass($override)) {
                continue;
            }
            $class = $this->reflectionProvider->getClass($override)->getNativeReflection();
            do {
                $file = $class->getFileName();
                if ($file !== false) {
                    $scope->trackFileDependency($file);
                }
                $class = $class->getParentClass();
            } while ($class !== false);
        }

        return [];
    }
}
