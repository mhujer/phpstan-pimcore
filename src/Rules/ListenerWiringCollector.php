<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Node\InClassNode;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Rules\MetadataRuleError;

/**
 * Collect before reporting XML candidates: a subscriber declaration can follow the listener method.
 * @implements Collector<Node, array{owned: list<string>, candidates: list<array{message: string, line: int, pair: string, declarationPair: string}>}>
 */
final class ListenerWiringCollector implements Collector
{
    public function __construct(private readonly ListenerWiringRule $rule)
    {
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /** @return array{owned: list<string>, candidates: list<array{message: string, line: int, pair: string, declarationPair: string}>}|null */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if ($node::class !== InClassMethodNode::class && $node::class !== InClassNode::class && $node::class !== MethodReturnStatementsNode::class) {
            return null;
        }
        $owned = [];
        foreach ($this->rule->declaredErrors($node, $scope) as $error) {
            if (!$error instanceof MetadataRuleError) {
                continue;
            }
            $pair = $error->getMetadata()['listenerPair'] ?? null;
            if (!is_string($pair)) {
                continue;
            }

            $owned[] = $pair;
        }
        $candidates = [];
        if ($node::class === InClassMethodNode::class) {
            foreach ($this->rule->xmlErrors($node, $scope) as $error) {
                $metadata = $error->getMetadata();
                $pair = $metadata['listenerPair'] ?? null;
                $declaration = $metadata['listenerDeclarationPair'] ?? null;
                if (!is_string($pair) || !is_string($declaration)) {
                    continue;
                }
                $candidates[] = [
                    'message' => $error->getMessage(),
                    'line' => $node->getOriginalNode()->getStartLine(),
                    'pair' => $pair,
                    'declarationPair' => $declaration,
                ];
            }
        }

        return ['owned' => $owned, 'candidates' => $candidates];
    }
}
