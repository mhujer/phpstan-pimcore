<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Pimcore\Model\AbstractModel;

/**
 * Constant keys and their expressions, written directly in a values array or setValue() call.
 */
final class ValuesArray
{
    public static function usesNativeSetters(ClassReflection $class): bool
    {
        foreach (['setValues', 'setValue'] as $method) {
            if (!$class->hasNativeMethod($method) || $class->getNativeMethod($method)->getDeclaringClass()->getName() !== AbstractModel::class) {
                return false;
            }
        }

        return true;
    }

    /**
     * Arguments must already be in parameter order. Unpacked items and nonconstant keys are skipped.
     *
     * @param list<Arg> $args
     * @return list<array{int|string, Expr}>
     */
    public static function read(string $methodName, array $args, Scope $scope): array
    {
        if (!isset($args[0]) || $args[0]->unpack) {
            return [];
        }

        if (strtolower($methodName) === 'setvalue') {
            if (!isset($args[1]) || $args[1]->unpack) {
                return [];
            }

            return array_map(static fn (int|string $key): array => [$key, $args[1]->value], self::keys($args[0]->value, $scope));
        }

        if (!$args[0]->value instanceof Array_) {
            return [];
        }

        $entries = [];
        $arrays = $scope->getType($args[0]->value)->getConstantArrays();
        $literalKeys = count($arrays) === 1 && count($arrays[0]->getKeyTypes()) === count($args[0]->value->items) ? $arrays[0]->getKeyTypes() : [];
        foreach ($args[0]->value->items as $index => $item) {
            if ($item->unpack) {
                continue;
            }

            // PHPStan resolves implicit integer indexes, including indexes after explicit integer keys.
            $keys = $item->key === null ? (isset($literalKeys[$index]) ? [$literalKeys[$index]->getValue()] : []) : self::keys($item->key, $scope);
            foreach ($keys as $key) {
                $entries[] = [$key, $item->value];
            }
        }

        return $entries;
    }

    /** @return list<int|string> */
    private static function keys(Expr $expr, Scope $scope): array
    {
        $type = $scope->getType($expr);
        if (!$type->isConstantScalarValue()->yes()) {
            return [];
        }

        return array_values(array_filter($type->getConstantScalarValues(), static fn (mixed $value): bool => is_string($value) || is_int($value)));
    }
}
