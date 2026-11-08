<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Type\ClassOverride;
use Mhujer\PHPStan\Pimcore\Type\GeneratedGetters;
use Mhujer\PHPStan\Pimcore\Type\RegisteredClassOverrides;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\Document;

/** @implements Rule<Node> */
final class ClassOverrideRegistrationRule implements Rule
{
    public function __construct(private readonly RegisteredClassOverrides $overrides)
    {
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!($node instanceof New_ || $node instanceof StaticCall || $node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof Foreach_)
            || !$this->overrides->configured($scope)
        ) {
            return [];
        }
        if ($node instanceof StaticCall && $node->name instanceof Identifier && $this->isStaticLoad($node, $scope)) {
            $errors = $this->invalidTypes($scope->getType($node), $scope);
            $type = $node->class instanceof Name ? new ObjectType($scope->resolveName($node->class)) : $scope->getType($node->class)->getClassStringObjectType();
            foreach ($type->getObjectClassReflections() as $class) {
                if ($class->isAbstract() || !$this->overrides->loadedClass($class, $scope)?->isNull()->yes()) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(sprintf('Static load on %s does not use a registered class override.', $class->getName()))
                    ->identifier('pimcore.unregisteredClassOverride')->build();
            }

            return $errors;
        }
        if (($node instanceof MethodCall || $node instanceof NullsafeMethodCall) && $node->name instanceof Identifier) {
            $receiver = $scope->getType($node->var);
            $name = $node->name->name;
            $relation = str_starts_with($name, 'get') && GeneratedGetters::of($receiver) !== null;
            $metadata = in_array($name, ['getObject', 'getElement'], true)
                && ((new ObjectType(ObjectMetadata::class))->isSuperTypeOf($receiver)->yes() || (new ObjectType(ElementMetadata::class))->isSuperTypeOf($receiver)->yes());
            $listing = (new ObjectType(Listing::class))->isSuperTypeOf($receiver)->yes()
                && in_array($name, ['load', 'getData', 'getObjects', 'getItems', 'current'], true);

            return $relation || $metadata || $listing ? $this->invalidTypes($scope->getType($node), $scope) : [];
        }
        if ($node instanceof Foreach_) {
            $receiver = $scope->getType($node->expr);

            return (new ObjectType(Listing::class))->isSuperTypeOf($receiver)->yes()
                ? $this->invalidTypes($receiver->getIterableValueType(), $scope) : [];
        }
        if (!($node instanceof New_ || ($node instanceof StaticCall && $node->name instanceof Identifier && strtolower($node->name->name) === 'create'))
        ) {
            return [];
        }
        $type = $node->class instanceof Name
            ? new ObjectType($scope->resolveName($node->class))
            : ($node->class instanceof Node\Expr ? $scope->getType($node->class)->getClassStringObjectType() : null);
        if ($type === null) {
            return [];
        }
        $errors = [];
        foreach ($type->getObjectClassReflections() as $class) {
            if (!str_starts_with($class->getName(), 'Pimcore\\Model\\DataObject\\')) {
                continue;
            }
            $key = str_ends_with($class->getName(), '\\Listing') ? substr($class->getName(), 0, -8) : $class->getName();
            if (!$this->overrides->isValid($class->getName(), $scope) && !$this->overrides->isValid($key, $scope)) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf('Construction of %s bypasses its registered class override.', $class->getName()))
                ->identifier('pimcore.bypassedClassOverride')->build();
        }

        return $errors;
    }
    private function isStaticLoad(StaticCall $call, Scope $scope): bool
    {
        if (!$call->name instanceof Identifier) {
            return false;
        }
        $name = $call->name->name;
        $type = $call->class instanceof Name ? new ObjectType($scope->resolveName($call->class)) : $scope->getType($call->class)->getClassStringObjectType();
        $method = $scope->getMethodReflection($type, $name);
        if ($method === null) {
            return false;
        }
        if (in_array(strtolower($name), ['getbyid', 'getbypath'], true)) {
            if ($method->getDeclaringClass()->getName() === AbstractObject::class) {
                return true;
            }
            if (in_array($method->getDeclaringClass()->getName(), [Document::class, Asset::class], true)) {
                foreach ($type->getObjectClassNames() as $class) {
                    if ($this->overrides->entry($class, $scope) !== null) {
                        return true;
                    }
                }
            }

            return false;
        }
        if ($name !== 'getList' && !str_starts_with($name, 'getBy')) {
            return false;
        }
        if ($name === 'getList') {
            foreach ($call->getArgs() as $arg) {
                if ($arg->unpack || !$scope->getType($arg->value)->hasOffsetValueType(new ConstantStringType('class'))->no()) {
                    return false;
                }
            }
        }

        return ClassOverride::calledType($call, $scope, $name, $this->overrides) !== null;
    }

    /** @return list<IdentifierRuleError> */
    private function invalidTypes(Type $type, Scope $scope): array
    {
        $keys = [];
        if ($type->isIterable()->yes()) {
            $type = TypeCombinator::union($type, $type->getIterableValueType());
        }
        TypeTraverser::map($type, function (Type $inner, callable $traverse) use ($scope, &$keys): Type {
            foreach ($inner->getObjectClassNames() as $key) {
                if ($this->overrides->entry($key, $scope) === null || $this->overrides->isValid($key, $scope)) {
                    continue;
                }

                $keys[$key] = true;
            }

            return $traverse($inner);
        });
        $errors = [];
        foreach (array_keys($keys) as $key) {
            $errors[] = RuleErrorBuilder::message(sprintf('Invalid class override for %s: the registered class must exist and extend it.', $key))
                ->identifier('pimcore.invalidClassOverride')->build();
        }

        return $errors;
    }
}
