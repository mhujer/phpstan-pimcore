<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/** @implements Rule<CollectedDataNode> */
final class CacheKeyMismatchRule implements Rule
{
    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $calls = [];
        foreach ($node->get(CacheKeyCallCollector::class) as $collected) {
            foreach ($collected as $call) {
                $calls[$call['file']][$call['position']] = $call;
            }
        }

        $errors = [];
        foreach ($node->get(CacheKeyPairCollector::class) as $file => $collected) {
            foreach ($collected as $pairs) {
                foreach ($pairs as $pair) {
                    $load = $calls[$file][$pair['load']] ?? null;
                    $save = $calls[$file][$pair['save']] ?? null;
                    if ($load === null || $save === null) {
                        continue;
                    }

                    $bothConstant = $load['constant'] !== null && $save['constant'] !== null;
                    if ($bothConstant ? $load['constant'] === $save['constant'] : !$pair['contains']) {
                        continue;
                    }

                    $errors[] = RuleErrorBuilder::message(sprintf('Cache save key differs from the key used to load $%s in the same function.', $pair['variable']))
                        ->identifier('pimcore.cacheKeyMismatch')
                        ->file($file)
                        ->line($save['line'])
                        ->build();
                }
            }
        }

        return $errors;
    }
}
