<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;

/**
 * A method call, nullsafe or static, with a literal name: the calls the collectors look at. Dynamic names
 * (`$cls::$m()`) can't be resolved.
 */
final readonly class NamedCall
{
    private function __construct(
        public MethodCall|NullsafeMethodCall|StaticCall $node,
        public string $name,
    ) {
    }

    public static function of(Node $node): ?self
    {
        if (!$node instanceof MethodCall && !$node instanceof NullsafeMethodCall && !$node instanceof StaticCall) {
            return null;
        }

        return $node->name instanceof Identifier ? new self($node, $node->name->name) : null;
    }

    /**
     * The type the method is called on: the object, or for a static call the class (`Foo::`, `$class::`).
     */
    public function receiverType(Scope $scope): Type
    {
        if (!$this->node instanceof StaticCall) {
            return $scope->getType($this->node->var);
        }

        if ($this->node->class instanceof Name) {
            return $scope->resolveTypeByName($this->node->class);
        }

        return $scope->getType($this->node->class)->getObjectTypeOrClassStringObjectType();
    }

    /**
     * The file the call is written in. In a trait, the scope's file is the file of the class using it, so this is
     * the trait's file instead.
     */
    public function file(Scope $scope): string
    {
        return $scope->getTraitReflection()?->getFileName() ?? $scope->getFile();
    }
}
