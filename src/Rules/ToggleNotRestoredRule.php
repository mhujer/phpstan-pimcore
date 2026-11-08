<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * A toggle flipped in a function must be restored in a `finally` of the same function. The calls come from
 * {@see ToggleCallCollector}, which knows their types, and {@see ToggleScopeCollector}, which knows where they are.
 * The flip and both errors are reported on the first call of a group in a function.
 *
 * @implements Rule<CollectedDataNode>
 */
final readonly class ToggleNotRestoredRule implements Rule
{
    public function __construct(
        private Toggles $toggles,
    ) {
    }

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
        $scopes = $node->get(ToggleScopeCollector::class);

        // [file][group][position] => call. A trait's calls are collected once per class using it, under the class's
        // file, so they are keyed by the file they are in and deduplicated by position.
        $calls = [];
        foreach ($node->get(ToggleCallCollector::class) as $collected) {
            foreach ($collected as ['file' => $file, 'position' => $position, 'line' => $line, 'group' => $group, 'toggle' => $toggle]) {
                $calls[$file][$group][$position] = ['line' => $line, 'toggle' => $toggle];
            }
        }

        $errors = [];
        foreach ($calls as $file => $groups) {
            $placement = [];
            foreach ($scopes[$file] ?? [] as $collected) {
                foreach ($collected as ['position' => $position, 'function' => $function, 'inFinally' => $inFinally]) {
                    $placement[$position] = ['function' => $function, 'inFinally' => $inFinally];
                }
            }

            foreach ($groups as $group => $groupCalls) {
                ksort($groupCalls);
                $byFunction = [];
                foreach ($groupCalls as $position => $call) {
                    // Without the file's scopes (a trait outside the analysed paths), the call can't be placed.
                    if (!isset($placement[$position])) {
                        continue;
                    }

                    $byFunction[$placement[$position]['function']][] = $call + ['inFinally' => $placement[$position]['inFinally']];
                }

                foreach ($byFunction as $functionCalls) {
                    $error = $this->check($functionCalls, $group, $file);
                    if ($error === null) {
                        continue;
                    }

                    $errors[] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * @param non-empty-list<array{line: int, toggle: string, inFinally: bool}> $calls in source order
     */
    private function check(array $calls, int $group, string $file): ?IdentifierRuleError
    {
        $flip = $calls[0];
        $restores = array_slice($calls, 1);
        if ($restores === []) {
            $message = sprintf('Toggle %s is flipped and never restored in the same function.', $flip['toggle']);
            $identifier = 'pimcore.toggleNotRestored';
        } else {
            foreach ($restores as $restore) {
                if ($restore['inFinally']) {
                    return null;
                }
            }

            $message = sprintf('Toggle %s is restored outside a finally block.', $flip['toggle']);
            $identifier = 'pimcore.toggleNotRestoredInFinally';
        }

        $error = RuleErrorBuilder::message($message)
            ->identifier($identifier)
            ->file($file)
            ->line($flip['line']);
        $tip = $this->toggles->tipFor($group);
        if ($tip !== null) {
            $error->tip($tip);
        }

        return $error->build();
    }
}
