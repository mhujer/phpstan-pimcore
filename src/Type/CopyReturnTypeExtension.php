<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class CopyReturnTypeExtension implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    /**
     * @param class-string $declaringClass
     */
    public function __construct(
        private readonly string $declaringClass,
        private readonly string $method,
        private readonly int $sourcePosition,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return strtolower($methodReflection->getName()) === strtolower($this->method)
            && $methodReflection->getDeclaringClass()->getName() === $this->declaringClass;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isMethodSupported($methodReflection);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        return $this->sourceType($methodReflection, $methodCall->getArgs(), $scope);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        return $this->sourceType($methodReflection, $methodCall->getArgs(), $scope);
    }

    /**
     * @param array<Arg> $args
     */
    private function sourceType(MethodReflection $methodReflection, array $args, Scope $scope): ?Type
    {
        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $args, $methodReflection->getVariants());
        $args = ArgumentsNormalizer::reorderArgs($variant, $args);
        $argument = $args[$this->sourcePosition] ?? null;

        return $argument === null || $argument->unpack ? null : $scope->getType($argument->value);
    }
}
