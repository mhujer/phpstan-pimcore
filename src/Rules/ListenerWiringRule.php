<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Node\InClassNode;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\MetadataRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/** @implements Rule<Node> */
final class ListenerWiringRule implements Rule
{
    public function __construct(private readonly ContainerXmlReader $reader, private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node::class === CollectedDataNode::class) {
            $owned = [];
            $candidates = [];
            foreach ($node->get(ListenerWiringCollector::class) as $file => $entries) {
                foreach ($entries as $entry) {
                    foreach ($entry['owned'] as $message) {
                        $owned[$message] = true;
                    }
                    foreach ($entry['candidates'] as $candidate) {
                        $candidates[$candidate['pair']] = [$file, $candidate];
                    }
                }
            }
            $errors = [];
            foreach ($candidates as $pair => [$file, $candidate]) {
                if (isset($owned[$pair]) || isset($owned[$candidate['declarationPair']])) {
                    continue;
                }
                $errors[$candidate['message']] = RuleErrorBuilder::message($candidate['message'])->identifier('pimcore.listenerEventType')->file($file)->line($candidate['line'])->build();
            }

            return array_values($errors);
        }
        return $this->declaredErrors($node, $scope);
    }

    /** @return list<IdentifierRuleError> */
    public function declaredErrors(Node $node, Scope $scope): array
    {
        if ($node::class === InClassMethodNode::class) {
            return $this->attributeErrors($node->getOriginalNode()->attrGroups, $scope, $node->getClassReflection(), $node->getOriginalNode()->name->toString());
        }
        if ($node::class === InClassNode::class) {
            return $this->attributeErrors($node->getOriginalNode()->attrGroups, $scope, $node->getClassReflection(), null);
        }
        if ($node::class === MethodReturnStatementsNode::class && strtolower($node->getMethodName()) === 'getsubscribedevents'
            && $node->getClassReflection()->implementsInterface('Symfony\\Component\\EventDispatcher\\EventSubscriberInterface')) {
            $errors = [];
            $seen = [];
            foreach ($node->getReturnStatements() as $return) {
                $expression = $return->getReturnNode()->expr;
                if ($expression === null) {
                    continue;
                }
                $type = $return->getScope()->getType($expression);
                if (!$type->isConstantArray()->yes() || count($type->getConstantArrays()) !== 1) {
                    continue;
                }
                foreach ($type->getConstantArrays()[0]->getKeyTypes() as $index => $key) {
                    if ($this->constantString($key) === null) {
                        continue;
                    }
                    foreach ($this->subscriberMethods($type->getConstantArrays()[0]->getValueTypes()[$index]) as $method) {
                        $eventName = $this->constantString($key);
                        $pair = $eventName . ':' . $method;
                        if (isset($seen[$pair])) {
                            continue;
                        }
                        $seen[$pair] = true;
                        array_push($errors, ...$this->pairErrors($node->getClassReflection(), $method, $eventName, $node->getStartLine()));
                    }
                }
            }
            return $errors;
        }
        return [];
    }

    /** @return list<IdentifierRuleError&MetadataRuleError> */
    public function xmlErrors(InClassMethodNode $node, Scope $scope): array
    {
        if ($scope instanceof DependencyTracker) {
            $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('listeners'));
        }
        $errors = [];
        $class = $node->getClassReflection();
        $method = $node->getOriginalNode()->name->toString();
        foreach ($this->reader->listeners() as $listener) {
            $event = $listener[0] ?? null;
            $callback = $listener[1] ?? null;
            if (!is_string($event) || !isset(EventMap::EVENTS[$event]) || !is_array($callback)
                || !is_string($callback[1] ?? null) || strcasecmp($callback[1], $method) !== 0) {
                continue;
            }
            $reference = $callback[0] ?? null;
            if (!is_array($reference) || !in_array($reference['type'] ?? null, ['service', 'service_closure'], true)
                || !is_string($reference['id'] ?? null)) {
                continue;
            }
            if ($scope instanceof DependencyTracker) {
                $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('serviceClass', $reference['id']));
            }
            $serviceClass = $this->reader->serviceClass($reference['id']);
            if ($serviceClass !== null && $scope instanceof DependencyTracker) {
                $scope->trackClassDependency($serviceClass);
            }
            if ($serviceClass === null || !$this->reflectionProvider->hasClass($serviceClass)) {
                continue;
            }
            $service = $this->reflectionProvider->getClass($serviceClass);
            if (!$service->hasNativeMethod($method)
                || $service->getNativeMethod($method)->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }
            foreach ($this->pairErrors($class, $method, $event, $node->getOriginalNode()->getStartLine(), $service->getName()) as $error) {
                $errors[$service->getName() . ':' . $event] = $error;
            }
        }

        return array_values($errors);
    }

    private static function pairKey(string $class, string $method, string $event): string
    {
        return json_encode([strtolower($class), strtolower($method), $event], JSON_THROW_ON_ERROR);
    }

    private function constantString(Type $type): ?string
    {
        $strings = $type->getConstantStrings();
        return $type->isConstantScalarValue()->yes() && count($strings) === 1 ? $strings[0]->getValue() : null;
    }

    /** @return list<string> */
    private function subscriberMethods(Type $type): array
    {
        $string = $this->constantString($type);
        if ($string !== null) {
            return [$string];
        }
        $offset = new ConstantIntegerType(0);
        if (!$type->isConstantArray()->yes() || count($type->getConstantArrays()) !== 1 || !$type->hasOffsetValueType($offset)->yes()) {
            return [];
        }
        $first = $this->constantString($type->getOffsetValueType($offset));
        if ($first !== null) {
            return [$first];
        }
        $methods = [];
        foreach ($type->getConstantArrays()[0]->getValueTypes() as $entry) {
            if (!$entry->isConstantArray()->yes() || !$entry->hasOffsetValueType($offset)->yes()) {
                continue;
            }
            $name = $this->constantString($entry->getOffsetValueType($offset));
            if ($name === null) {
                continue;
            }

            $methods[] = $name;
        }
        return $methods;
    }

    /**
     * @param array<AttributeGroup> $groups
     * @return list<IdentifierRuleError>
     */
    private function attributeErrors(array $groups, Scope $scope, ClassReflection $class, ?string $declaredMethod): array
    {
        $errors = [];
        foreach ($groups as $group) {
            foreach ($group->attrs as $attribute) {
                if ($scope->resolveName($attribute->name) !== 'Symfony\\Component\\EventDispatcher\\Attribute\\AsEventListener') {
                    continue;
                }
                $eventName = null;
                $method = $declaredMethod;
                $unresolved = false;
                foreach ($attribute->args as $index => $arg) {
                    $name = $arg->name?->toString() ?? ['event', 'method', 'priority', 'dispatcher'][$index] ?? '';
                    $strings = $scope->getType($arg->value)->getConstantStrings();
                    if (count($strings) !== 1 || !$scope->getType($arg->value)->isConstantScalarValue()->yes()) {
                        if (in_array($name, ['event', 'method'], true) && !$scope->getType($arg->value)->isNull()->yes()) {
                            $unresolved = true;
                        }
                        continue;
                    }
                    if ($name === 'event') {
                        $eventName = $strings[0]->getValue();
                    }
                    if ($name !== 'method' || $declaredMethod !== null) {
                        continue;
                    }

                    $method = $strings[0]->getValue();
                }
                if ($unresolved) {
                    continue;
                }
                if ($eventName === null) {
                    array_push($errors, ...$this->missingNameErrors($class, $method ?? '__invoke', $attribute->getStartLine()));
                    continue;
                }
                if ($method === null) {
                    $method = 'on' . str_replace(' ', '', ucwords(str_replace(['.', '-', '_', '\\'], ' ', $eventName)));
                    if (!$class->hasNativeMethod($method)) {
                        $method = '__invoke';
                    }
                }
                array_push($errors, ...$this->pairErrors($class, $method, $eventName, $attribute->getStartLine()));
            }
        }
        return $errors;
    }

    /** @return list<IdentifierRuleError> */
    private function missingNameErrors(ClassReflection $class, string $method, int $line): array
    {
        if (!$class->hasNativeMethod($method)) {
            return [];
        }
        $parameter = $class->getNativeMethod($method)->getVariants()[0]->getParameters()[0] ?? null;
        if ($parameter === null) {
            return [];
        }
        foreach ($parameter->getNativeType()->getObjectClassNames() as $name) {
            if (!str_starts_with($name, 'Pimcore\\')) {
                continue;
            }
            $type = new ObjectType($name);
            foreach (array_unique(EventMap::EVENTS) as $eventClass) {
                if (!$type->isSuperTypeOf(new ObjectType($eventClass))->yes()) {
                    continue;
                }
                return [RuleErrorBuilder::message(sprintf('#[AsEventListener] on %s::%s() has no event name, so Symfony listens for %s, which Pimcore never dispatches. Set event:, e.g. DataObjectEvents::POST_UPDATE.', $class->getName(), $method, $name))->identifier('pimcore.listenerWithoutEventName')->line($line)->build()];
            }
        }
        return [];
    }

    /** @return list<IdentifierRuleError&MetadataRuleError> */
    private function pairErrors(ClassReflection $class, string $method, string $eventName, int $line, ?string $receiver = null): array
    {
        if (!isset(EventMap::EVENTS[$eventName]) || !$class->hasNativeMethod($method)) {
            return [];
        }
        $parameter = $class->getNativeMethod($method)->getVariants()[0]->getParameters()[0] ?? null;
        $eventClass = EventMap::EVENTS[$eventName];
        if ($parameter === null || $parameter->getNativeType() instanceof MixedType || !$parameter->getType()->isSuperTypeOf(new ObjectType($eventClass))->no()) {
            return [];
        }
        return [RuleErrorBuilder::message(sprintf('Listener %s::%s() for event %s expects %s, but Pimcore dispatches %s.', $class->getName(), $method, $eventName, $parameter->getType()->describe(VerbosityLevel::typeOnly()), $eventClass))->identifier('pimcore.listenerEventType')->line($line)->metadata([
            'listenerPair' => self::pairKey($receiver ?? $class->getName(), $method, $eventName),
            'listenerDeclarationPair' => self::pairKey($class->getNativeMethod($method)->getDeclaringClass()->getName(), $method, $eventName),
        ])->build()];
    }
}
