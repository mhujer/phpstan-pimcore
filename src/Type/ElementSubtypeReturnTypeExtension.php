<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ElementSubtypeReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @param class-string $declaringClass
     * @param list<string> $subtypes
     */
    public function __construct(
        private readonly string $declaringClass,
        private readonly RegisteredClassOverrides $overrides,
        private readonly array $subtypes = [],
        private readonly ?string $mapParameter = null,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === 'gettype'
            && $methodReflection->getDeclaringClass()->getName() === $this->declaringClass;
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        unset($methodReflection);
        $subtypes = $this->subtypes;
        if ($this->mapParameter !== null) {
            $map = $this->overrides->subtypeMap($this->mapParameter, $scope);
            if ($map === null) {
                return null;
            }
            $receiver = $scope->getType($methodCall->var);
            $matching = array_filter($map, static fn (Type $mapped): bool => $mapped->isSuperTypeOf($receiver)->yes() || $receiver->isSuperTypeOf($mapped)->yes());
            $subtypes = array_keys($matching === [] ? $map : $matching);
        }

        return TypeCombinator::union(...array_map(static fn (string $subtype): ConstantStringType => new ConstantStringType($subtype), $subtypes));
    }
}
