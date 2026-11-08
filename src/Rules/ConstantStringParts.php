<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\InterpolatedString;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;

/** The literal text a string expression is built from: concatenations, interpolations, sprintf() formats and constants. */
final class ConstantStringParts
{
    private function __construct()
    {
    }

    /**
     * The expression's segments in source order; each segment lists the constant values it may take.
     * A sprintf() segment holds each possible format with its conversions removed.
     *
     * @return list<list<string>>
     */
    public static function segments(Expr $expr, Scope $scope, ReflectionProvider $reflectionProvider): array
    {
        if ($expr instanceof Concat) {
            return [...self::segments($expr->left, $scope, $reflectionProvider), ...self::segments($expr->right, $scope, $reflectionProvider)];
        }

        if ($expr instanceof InterpolatedString) {
            $segments = [];
            foreach ($expr->parts as $part) {
                $segments = [...$segments, ...($part instanceof InterpolatedStringPart ? [[$part->value]] : self::segments($part, $scope, $reflectionProvider))];
            }

            return $segments;
        }

        if ($expr instanceof FuncCall) {
            $literals = [];
            foreach (self::sprintfFormats($expr, $scope, $reflectionProvider) as $format) {
                // Custom padding may itself contain a reserved character; it belongs to the conversion.
                $literal = preg_replace_callback(
                    "/%(?:%|(?:[0-9]+\\$)?(?:[-+ 0]|'.)*[0-9]*(?:\\.[0-9]+)?[bcdeEfFgGhHosuxX])/",
                    static fn (array $match): string => $match[0] === '%%' ? '%' : '',
                    $format,
                );
                if ($literal === null) {
                    continue;
                }

                $literals[] = $literal;
            }

            return $literals === [] ? [] : [$literals];
        }

        $values = array_map(static fn (ConstantStringType $string): string => $string->getValue(), $scope->getType($expr)->getConstantStrings());

        return $values === [] ? [] : [$values];
    }

    /**
     * The constant formats a sprintf() call may use; empty for any other call.
     *
     * @return list<string>
     */
    public static function sprintfFormats(FuncCall $call, Scope $scope, ReflectionProvider $reflectionProvider): array
    {
        if (!$call->name instanceof Name || !$reflectionProvider->hasFunction($call->name, $scope)) {
            return [];
        }

        $function = $reflectionProvider->getFunction($call->name, $scope);
        if (strtolower($function->getName()) !== 'sprintf') {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $call->getArgs(), $function->getVariants()),
            $call->getArgs(),
        );
        if (!isset($args[0]) || $args[0]->unpack) {
            return [];
        }

        return array_map(static fn (ConstantStringType $format): string => $format->getValue(), $scope->getType($args[0]->value)->getConstantStrings());
    }
}
