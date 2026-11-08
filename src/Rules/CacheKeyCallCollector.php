<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Pimcore\Cache;

/**
 * @phpstan-type CacheKeyCall array{file: string, position: int, line: int, constant: string|null}
 * @implements Collector<StaticCall, CacheKeyCall>
 */
final class CacheKeyCallCollector implements Collector
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /** @return CacheKeyCall|null */
    public function processNode(Node $node, Scope $scope): ?array
    {
        $call = NamedCall::of($node);
        if ($call === null || !$node->name instanceof Identifier || $node->isFirstClassCallable()) {
            return null;
        }

        $name = strtolower($node->name->name);
        if (!in_array($name, ['load', 'save'], true)) {
            return null;
        }

        $type = $call->receiverType($scope);
        if ($type->getObjectClassNames() !== [Cache::class]) {
            return null;
        }

        $method = $scope->getMethodReflection($type, $call->name);
        if ($method === null || $method->getDeclaringClass()->getName() !== Cache::class) {
            return null;
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node->getArgs(),
        );
        $position = $name === 'load' ? 0 : 1;
        if (!isset($args[$position]) || $args[$position]->unpack) {
            return null;
        }

        $key = $scope->getType($args[$position]->value);
        $strings = $key->getConstantStrings();

        return [
            'file' => $call->file($scope),
            'position' => $node->getStartFilePos(),
            'line' => $node->getStartLine(),
            'constant' => count($strings) === 1 && $key->equals($strings[0]) ? $strings[0]->getValue() : null,
        ];
    }
}
