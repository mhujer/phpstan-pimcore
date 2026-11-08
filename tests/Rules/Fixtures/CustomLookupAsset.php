<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\Asset;

final class CustomLookupAsset extends Asset
{
    /**
     * @param array<string, mixed> $params
     */
    public static function getById(int $id, array $params = []): ?static
    {
        return ($params['x'] ?? null) === $id ? new static() : null;
    }
}
