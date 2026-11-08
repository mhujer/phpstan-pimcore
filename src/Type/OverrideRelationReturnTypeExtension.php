<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/** Generated object relation getters use the model factory, whatever class owns the relation. */
final class OverrideRelationReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /** @param class-string $declaringClass */
    public function __construct(private readonly string $declaringClass, private readonly RegisteredClassOverrides $overrides)
    {
    }

    public function getClass(): string
    {
        return $this->declaringClass;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return str_starts_with($methodReflection->getName(), 'get');
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (!$this->overrides->configured($scope) || GeneratedGetters::of($scope->getType($methodCall->var)) === null) {
            return null;
        }
        $declared = ParametersAcceptorSelector::selectFromArgs($scope, $methodCall->getArgs(), $methodReflection->getVariants())->getReturnType();
        $mapped = $this->overrides->replace($declared, $scope, true);

        return $mapped->equals($declared) ? null : $mapped;
    }
}
