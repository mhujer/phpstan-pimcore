<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

final class ScopedCallbackProvider
{
    public function result(): int
    {
        return 1;
    }

    /**
     * @template T
     * @param T $value
     * @return T
     */
    public function identity(mixed $value): mixed
    {
        return $value;
    }

    public function __invoke(): bool
    {
        return true;
    }
}
