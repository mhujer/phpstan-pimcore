<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeFinder;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\FileNode;

/**
 * @phpstan-type CacheKeyPair array{load: int, save: int, variable: string, contains: bool}
 * @implements Collector<FileNode, list<CacheKeyPair>>
 */
final class CacheKeyPairCollector implements Collector
{
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @return list<CacheKeyPair>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $pairs = [];
        $functions = (new NodeFinder())->findInstanceOf($node->getNodes(), FunctionLike::class);
        foreach ($functions as $function) {
            $nodes = $this->ownNodes(array_values($function->getStmts() ?? []));
            if ($this->hasAmbiguousFlow($nodes)) {
                continue;
            }

            $loads = [];
            foreach ($nodes as $child) {
                if (!($child instanceof Assign) || !($child->var instanceof Variable) || !is_string($child->var->name)
                    || !($child->expr instanceof StaticCall) || !$this->isCall($child->expr, 'load')) {
                    continue;
                }

                $loads[$child->var->name][] = $child->expr;
            }

            // Only a load in an unconditional statement can dominate the later save.
            foreach ($function->getStmts() ?? [] as $statement) {
                if (!$statement instanceof Expression || !$statement->expr instanceof Assign) {
                    continue;
                }

                $assignment = $statement->expr;
                if (!$assignment->var instanceof Variable || !is_string($assignment->var->name)) {
                    continue;
                }

                $variable = $assignment->var->name;
                if (count($loads[$variable] ?? []) !== 1 || $assignment->expr !== $loads[$variable][0]) {
                    continue;
                }

                foreach ($nodes as $child) {
                    if (!$child instanceof StaticCall || !$this->isCall($child, 'save')
                        || $child->getStartFilePos() <= $assignment->getEndFilePos()) {
                        continue;
                    }

                    $data = null;
                    foreach ($child->getArgs() as $position => $argument) {
                        if ($argument->unpack || ($argument->name?->name !== 'data' && ($position !== 0 || $argument->name !== null))) {
                            continue;
                        }

                        $data = $argument->value;
                    }

                    if (!($data instanceof Variable) || $data->name !== $variable) {
                        continue;
                    }

                    $loadKey = $this->argument($loads[$variable][0], 0, 'key');
                    $saveKey = $this->argument($child, 1, 'key');
                    $pairs[] = [
                        'load' => $assignment->expr->getStartFilePos(),
                        'save' => $child->getStartFilePos(),
                        'variable' => $variable,
                        'contains' => $loadKey !== null && $saveKey !== null && $this->containsOtherKey($loadKey, $saveKey)
                            && $this->keysAreStable($loadKey, $saveKey, $assignment, $child, $nodes),
                    ];
                }
            }
        }

        return $pairs;
    }

    /** @param list<Node> $nodes */
    private function hasAmbiguousFlow(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof AssignRef || $node instanceof Unset_ || $node instanceof Global_ || $node instanceof Goto_
                || $node instanceof Eval_ || $node instanceof Include_ || ($node instanceof Variable && !is_string($node->name))) {
                return true;
            }

            if (!($node instanceof Closure)) {
                continue;
            }

            foreach ($node->uses as $use) {
                if ($use->byRef) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @param list<Node> $nodes */
    private function keysAreStable(Expr $loadKey, Expr $saveKey, Assign $load, StaticCall $save, array $nodes): bool
    {
        $variables = (new NodeFinder())->findInstanceOf([$loadKey, $saveKey], Variable::class);
        $names = array_map(static fn (Variable $variable): string|Expr => $variable->name, $variables);
        foreach ($nodes as $node) {
            if ($node->getStartFilePos() <= $load->getEndFilePos() || $node->getStartFilePos() >= $save->getStartFilePos()) {
                continue;
            }

            $target = match (true) {
                $node instanceof Assign, $node instanceof AssignOp, $node instanceof PreInc, $node instanceof PreDec,
                $node instanceof PostInc, $node instanceof PostDec => $node->var,
                // An intervening call may accept its argument by reference.
                $node instanceof Arg => $node->value,
                default => null,
            };
            if ($target instanceof Variable && in_array($target->name, $names, true)) {
                return false;
            }
        }

        return true;
    }

    private function argument(StaticCall $call, int $position, string $name): ?Expr
    {
        foreach ($call->getArgs() as $index => $argument) {
            if (!$argument->unpack && ($argument->name?->name === $name || ($index === $position && $argument->name === null))) {
                return $argument->value;
            }
        }

        return null;
    }

    private function containsOtherKey(Expr $left, Expr $right): bool
    {
        $leftParts = $this->keyParts($left);
        $rightParts = $this->keyParts($right);
        if ($leftParts === null || $rightParts === null || $leftParts === [] || $rightParts === [] || count($leftParts) === count($rightParts)) {
            return false;
        }

        [$long, $short] = count($leftParts) > count($rightParts) ? [$leftParts, $rightParts] : [$rightParts, $leftParts];
        for ($offset = 0; $offset <= count($long) - count($short); $offset++) {
            if (array_slice($long, $offset, count($short)) === $short) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string>|null */
    private function keyParts(Expr $key): ?array
    {
        if ($key instanceof Concat) {
            $left = $this->keyParts($key->left);
            $right = $this->keyParts($key->right);

            return $left === null || $right === null ? null : [...$left, ...$right];
        }

        if ($key instanceof String_ && $key->value === '') {
            return [];
        }

        if (($key instanceof Variable && is_string($key->name)) || ($key instanceof ClassConstFetch && $key->class instanceof Name && $key->name instanceof Identifier) || $key instanceof ConstFetch || $key instanceof String_) {
            return [(new Standard())->prettyPrintExpr($key)];
        }

        return null;
    }

    private function isCall(StaticCall $call, string $method): bool
    {
        return $call->name instanceof Identifier && strtolower($call->name->name) === $method && !$call->isFirstClassCallable();
    }

    /**
     * @param list<Node> $nodes
     * @return list<Node>
     */
    private function ownNodes(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[] = $node;
            if ($node instanceof FunctionLike) {
                continue;
            }
            foreach ($node->getSubNodeNames() as $name) {
                $child = $node->$name;
                if ($child instanceof Node) {
                    $result = [...$result, ...$this->ownNodes([$child])];
                } elseif (is_array($child)) {
                    $result = [...$result, ...$this->ownNodes(array_values(array_filter($child, static fn (mixed $value): bool => $value instanceof Node)))];
                }
            }
        }

        return $result;
    }
}
