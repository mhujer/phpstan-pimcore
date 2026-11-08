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
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ScopedCallbackReturnTypeExtension implements DynamicMethodReturnTypeExtension, DynamicStaticMethodReturnTypeExtension
{
    /**
     * @param class-string $declaringClass
     */
    public function __construct(
        private readonly string $declaringClass,
        private readonly string $method,
        private readonly int $callbackPosition,
    ) {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === $this->method;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $this->isMethodSupported($methodReflection);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        return $this->callbackReturnType($methodReflection, $methodCall->getArgs(), $scope);
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        return $this->callbackReturnType($methodReflection, $methodCall->getArgs(), $scope);
    }

    /**
     * @param array<Arg> $args
     */
    private function callbackReturnType(MethodReflection $methodReflection, array $args, Scope $scope): ?Type
    {
        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $args, $methodReflection->getVariants());
        $args = ArgumentsNormalizer::reorderArgs($variant, $args);
        $callback = $args[$this->callbackPosition] ?? null;
        if ($callback === null || $callback->unpack) {
            return null;
        }

        $type = $scope->getType($callback->value);
        if (!$type->isCallable()->yes()) {
            return null;
        }

        $returnTypes = [];
        foreach ($type->getCallableParametersAcceptors($scope) as $acceptor) {
            // Resolve templates without passing the scoped callback's runtime arguments.
            $returnType = ParametersAcceptorSelector::selectFromArgs($scope, [], [$acceptor])->getReturnType();
            $returnTypes[] = $returnType->isVoid()->yes() ? new NullType() : $returnType;
        }

        return TypeCombinator::union(...$returnTypes);
    }
}
