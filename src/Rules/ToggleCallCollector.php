<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * Collects every call to a toggle method, resolved through PHPStan's method reflection: the file the call is in (for a
 * trait, the trait's file), its position and line, its toggle group and the declaring `Class::method()`.
 *
 * @phpstan-type ToggleCall array{file: string, position: int, line: int, group: int, toggle: string}
 * @implements Collector<CallLike, ToggleCall>
 */
final readonly class ToggleCallCollector implements Collector
{
    public function __construct(
        private Toggles $toggles,
    ) {
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @return ToggleCall|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        $call = NamedCall::of($node);
        if ($call === null || !$this->toggles->isToggleMethodName($call->name)) {
            return null;
        }

        $receiver = $call->receiverType($scope);
        if (!$receiver->hasMethod($call->name)->yes()) {
            return null;
        }

        $method = $receiver->getMethod($call->name, $scope);
        $declaringClass = $method->getDeclaringClass()->getName();
        $group = $this->toggles->groupOf($declaringClass, $method->getName());
        if ($group === null) {
            return null;
        }

        return [
            'file' => $call->file($scope),
            'position' => $node->getStartFilePos(),
            'line' => $node->getStartLine(),
            'group' => $group,
            'toggle' => $declaringClass . '::' . $method->getName() . '()',
        ];
    }
}
