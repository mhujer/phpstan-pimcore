<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * @implements Rule<Expr>
 * @phpstan-import-type Service from ContainerXmlReader
 */
final class WorkflowNamesRule implements Rule
{
    public function __construct(private readonly ContainerXmlReader $reader)
    {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $call = NamedCall::of($node);
        if ($call === null || (!$node instanceof MethodCall && !$node instanceof NullsafeMethodCall) || $node->isFirstClassCallable()) {
            return [];
        }
        $type = $call->receiverType($scope);
        $methodName = strtolower($call->name);
        if (in_array($methodName, ['get', 'has'], true) && (new ObjectType(Registry::class))->isSuperTypeOf($type)->yes()) {
            [$kind, $index, $label, $identifier] = ['workflows', 1, 'Workflow', 'pimcore.unknownWorkflow'];
        } elseif (in_array($methodName, ['apply', 'can'], true) && (new ObjectType(WorkflowInterface::class))->isSuperTypeOf($type)->yes()) {
            [$kind, $index, $label, $identifier] = ['transitions', 1, 'Transition', 'pimcore.unknownWorkflowTransition'];
        } elseif ($methodName === 'has' && (new ObjectType(Marking::class))->isSuperTypeOf($type)->yes()) {
            [$kind, $index, $label, $identifier] = ['places', 0, 'Place', 'pimcore.unknownWorkflowPlace'];
        } else {
            return [];
        }
        $names = $this->names($scope);
        if ($names === null) {
            return [];
        }
        $method = $scope->getMethodReflection($type, $call->name);
        if ($method === null) {
            return [];
        }
        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants());
        $normalized = ArgumentsNormalizer::reorderArgs($variant, $node->getArgs());
        $argument = $normalized[$index] ?? null;
        if ($argument === null || $argument->unpack) {
            return [];
        }
        $errors = [];
        foreach ($scope->getType($argument->value)->getConstantStrings() as $literal) {
            if (in_array($literal->getValue(), $names[$kind], true)) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf("%s '%s' is not defined in any workflow.", $label, $literal->getValue()))
                ->identifier($identifier)->build();
        }

        return $errors;
    }

    /** @return array{workflows: list<string>, transitions: list<string>, places: list<string>}|null */
    private function names(Scope $scope): ?array
    {
        $this->track($scope, 'workflows');
        $this->track($scope, 'workflowServices');
        $definitions = $this->reader->workflows();
        if ($definitions === []) {
            return null;
        }
        $names = ['workflows' => [], 'transitions' => [], 'places' => []];
        foreach ($definitions as $id => $arguments) {
            $this->track($scope, 'service', $id);
            $definition = $this->reader->service($id);
            if ($definition === null || $definition['abstract'] || $definition['factory']) {
                return null;
            }
            $places = $arguments[0] ?? $arguments['$places'] ?? null;
            $transitions = $arguments[1] ?? $arguments['$transitions'] ?? null;
            if (!is_array($places) || !is_array($transitions)) {
                return null;
            }
            foreach ($places as $place) {
                if (!self::literal($place)) {
                    return null;
                }
                $names['places'][] = $place;
            }
            foreach ($transitions as $transition) {
                $service = $this->resolveService($transition, $scope);
                if ($service === null || $service['class'] === null || !(new ObjectType(Transition::class))->isSuperTypeOf(new ObjectType($service['class']))->yes()) {
                    return null;
                }
                $name = $service['arguments'][0] ?? $service['arguments']['$name'] ?? null;
                if (!self::literal($name)) {
                    return null;
                }
                $names['transitions'][] = $name;
            }
        }
        $wiredDefinitions = [];
        foreach ($this->reader->workflowServices() as $service) {
            if ($service['factory']) {
                return null;
            }
            $reference = $service['arguments'][0] ?? $service['arguments']['$definition'] ?? null;
            $name = match (true) {
                array_key_exists(3, $service['arguments']) => $service['arguments'][3],
                array_key_exists('$name', $service['arguments']) => $service['arguments']['$name'],
                default => 'unnamed',
            };
            if (!is_array($reference) || !is_string($reference['id'] ?? null) || !self::literal($name)) {
                return null;
            }
            $resolved = $this->reader->resolveService($reference['id']);
            foreach ($resolved['ids'] as $id) {
                $this->track($scope, 'service', $id);
            }
            $id = $resolved['ids'][array_key_last($resolved['ids'])];
            if ($resolved['service'] === null || !isset($definitions[$id])) {
                return null;
            }
            $wiredDefinitions[$id] = true;
            $names['workflows'][] = $name;
        }
        // A definition with unknown wiring may contribute another workflow name.
        if (count($wiredDefinitions) !== count($definitions)) {
            return null;
        }

        return $names;
    }

    /** @return Service|null */
    private function resolveService(mixed $value, Scope $scope): ?array
    {
        if (is_array($value) && ($value['type'] ?? null) === 'service' && is_string($value['id'] ?? null)) {
            $resolved = $this->reader->resolveService($value['id']);
            foreach ($resolved['ids'] as $id) {
                $this->track($scope, 'service', $id);
            }
            $value = $resolved['service'];
        }
        if (!is_array($value) || !is_string($value['class'] ?? null) || ($value['abstract'] ?? true) !== false || ($value['factory'] ?? true) !== false || !is_array($value['arguments'] ?? null)) {
            return null;
        }

        return ['class' => $value['class'], 'alias' => null, 'abstract' => false, 'factory' => false, 'arguments' => $value['arguments']];
    }

    /** @phpstan-assert-if-true string $value */
    private static function literal(mixed $value): bool
    {
        return is_string($value) && !str_contains($value, '%');
    }

    private function track(Scope $scope, string ...$parts): void
    {
        if (!($scope instanceof DependencyTracker)) {
            return;
        }

        $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key(...$parts));
    }
}
