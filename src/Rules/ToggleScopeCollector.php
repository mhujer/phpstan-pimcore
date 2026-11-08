<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;

/**
 * Collects, for every call in a file whose method name is a toggle method, the function it is in and whether it is in
 * a `finally` of that function. Every function, method, closure and arrow function is its own scope; code outside of
 * them is one more scope. This is syntax only; {@see ToggleCallCollector} tells which calls are toggles.
 *
 * @phpstan-type TogglePlacement array{position: int, function: int, inFinally: bool}
 * @implements Collector<FileNode, list<TogglePlacement>>
 */
final readonly class ToggleScopeCollector implements Collector
{
    /**
     * The scope of code outside any function.
     */
    public const int FILE_SCOPE = -1;

    public function __construct(
        private Toggles $toggles,
    ) {
    }

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @return list<TogglePlacement>|null the scope (`function`) is the function's position
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        $visitor = new class($this->toggles) extends NodeVisitorAbstract {
            /**
             * @var list<array{position: int, function: int, inFinally: bool}>
             */
            public array $calls = [];

            /**
             * The scope we're in: its position and how many of its `finally` blocks we're in.
             *
             * @var array{int, int}
             */
            private array $current = [ToggleScopeCollector::FILE_SCOPE, 0];

            /**
             * The scopes enclosing the current one, innermost last.
             *
             * @var list<array{int, int}>
             */
            private array $outer = [];

            public function __construct(
                private readonly Toggles $toggles,
            ) {
            }

            public function enterNode(Node $node): null
            {
                if ($node instanceof FunctionLike) {
                    $this->outer[] = $this->current;
                    $this->current = [$node->getStartFilePos(), 0];
                } elseif ($node instanceof Finally_) {
                    $this->current[1]++;
                } elseif ($this->isToggleCall($node)) {
                    $this->calls[] = [
                        'position' => $node->getStartFilePos(),
                        'function' => $this->current[0],
                        'inFinally' => $this->current[1] > 0,
                    ];
                }

                return null;
            }

            private function isToggleCall(Node $node): bool
            {
                $call = NamedCall::of($node);

                return $call !== null && $this->toggles->isToggleMethodName($call->name);
            }

            public function leaveNode(Node $node): null
            {
                if ($node instanceof FunctionLike) {
                    $this->current = array_pop($this->outer) ?? [ToggleScopeCollector::FILE_SCOPE, 0];
                } elseif ($node instanceof Finally_) {
                    $this->current[1]--;
                }

                return null;
            }
        };

        $traverser = new NodeTraverser($visitor);
        $traverser->traverse($node->getNodes());

        return $visitor->calls === [] ? null : $visitor->calls;
    }
}
