<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Pimcore\Cache;
use Pimcore\Cache\Core\CoreCacheHandler;

/** @implements Rule<Expr> */
final class CacheKeyRule implements Rule
{
    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $call = NamedCall::of($node);
        if ($call === null || $node instanceof NullsafeMethodCall || $call->node->isFirstClassCallable()) {
            return [];
        }

        $node = $call->node;
        $type = $call->receiverType($scope);
        $class = $node instanceof StaticCall ? Cache::class : CoreCacheHandler::class;
        if (!(new ObjectType($class))->isSuperTypeOf($type)->yes()) {
            return [];
        }

        $method = $scope->getMethodReflection($type, $call->name);
        if ($method === null || $method->getDeclaringClass()->getName() !== $class) {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node->getArgs(),
        );
        $name = strtolower($call->name);
        $label = $class . '::' . $method->getName() . '()';
        $position = match ($name) {
            'load', 'remove', 'getitem' => 0,
            'save' => $class === Cache::class ? 1 : 0,
            default => null,
        };
        $errors = [];
        if ($position !== null && isset($args[$position]) && !$args[$position]->unpack) {
            $errors = $this->expressionErrors($args[$position]->value, $scope, 'key', $label);
        }

        $tagPosition = match ($name) {
            'save' => 2,
            'cleartag', 'cleartags', 'addcleartagonshutdown', 'addignoredtagonsave', 'removeignoredtagonsave',
            'addignoredtagonclear', 'removeignoredtagonclear', 'addtagclearedonshutdown', 'addtagignoredonsave',
            'removetagignoredonsave', 'addtagignoredonclear', 'removetagignoredonclear' => 0,
            default => null,
        };
        if ($tagPosition === null || !isset($args[$tagPosition]) || $args[$tagPosition]->unpack) {
            return $errors;
        }

        $tags = $args[$tagPosition]->value;
        if (!in_array($name, ['save', 'cleartags'], true)) {
            return [...$errors, ...$this->expressionErrors($tags, $scope, 'tag', $label)];
        }

        if ($tags instanceof Array_) {
            foreach ($tags->items as $item) {
                if ($item->unpack) {
                    continue;
                }

                $errors = [...$errors, ...$this->expressionErrors($item->value, $scope, 'tag', $label)];
            }
        } else {
            foreach ($scope->getType($tags)->getConstantArrays() as $array) {
                foreach ($array->getValueTypes() as $value) {
                    $errors = [...$errors, ...$this->stringErrors($value, 'tag', $label)];
                }
            }
        }

        return $errors;
    }

    /** @return list<IdentifierRuleError> */
    private function expressionErrors(Expr $expr, Scope $scope, string $kind, string $label): array
    {
        $errors = [];
        $empty = match (true) {
            $expr instanceof InterpolatedString => false,
            // A conversion may print something, so only an empty format counts.
            $expr instanceof FuncCall => in_array('', ConstantStringParts::sprintfFormats($expr, $scope, $this->reflectionProvider), true),
            $expr instanceof Concat => in_array('', $scope->getType($expr)->getConstantScalarValues(), true),
            default => in_array('', array_map(static fn (ConstantStringType $string): string => $string->getValue(), $scope->getType($expr)->getConstantStrings()), true),
        };
        if ($empty) {
            $errors = $this->stringErrors(new ConstantStringType(''), $kind, $label);
            if ($expr instanceof Concat) {
                return $errors;
            }
        }

        // Report only the first segment with a reserved character.
        foreach (ConstantStringParts::segments($expr, $scope, $this->reflectionProvider) as $segment) {
            $segmentErrors = [];
            foreach ($segment as $value) {
                $segmentErrors = [...$segmentErrors, ...$this->stringErrors(new ConstantStringType($value), $kind, $label, false)];
            }
            if ($segmentErrors !== []) {
                return [...$errors, ...$segmentErrors];
            }
        }

        return $errors;
    }

    /** @return list<IdentifierRuleError> */
    private function stringErrors(Type $type, string $kind, string $label, bool $allowEmpty = true): array
    {
        $errors = [];
        foreach ($type->getConstantStrings() as $string) {
            $value = $string->getValue();
            if ($value === '' && $allowEmpty) {
                $errors[] = RuleErrorBuilder::message(sprintf('Cache %s passed to %s is empty.', $kind, $label))->identifier('pimcore.cacheEmptyKey')->build();
                continue;
            }

            $reserved = strpbrk($value, '{}()/\\@:');
            if ($reserved === false) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Cache %s "%s" passed to %s contains reserved character "%s".', $kind, $value, $label, $reserved[0]))
                ->identifier('pimcore.cacheReservedCharacters')
                ->tip('Symfony Cache rejects any of {}()/\\@: in keys and tags; use "_" or "-" instead.')
                ->build();
        }

        return $errors;
    }
}
