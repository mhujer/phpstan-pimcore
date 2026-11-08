<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ContainerXml;

use InvalidArgumentException;
use PHPStan\Analyser\ResultCache\ResultCacheValueExtension;

/**
 * Features declare the exact value read with Scope::trackValueDependency(ContainerXmlCache::class, self::key(...)).
 * Keys start with parameter, service, serviceClass, listeners, workflows or workflowServices; parameter keys include the subtree path.
 */
final class ContainerXmlCache implements ResultCacheValueExtension
{
    public function __construct(private readonly ContainerXmlReader $reader)
    {
    }

    public static function key(string ...$parts): string
    {
        return json_encode($parts, JSON_THROW_ON_ERROR);
    }

    public function getValue(string $key): string
    {
        $parts = json_decode($key, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($parts) || !array_is_list($parts) || $parts === [] || array_filter($parts, is_string(...)) !== $parts) {
            throw new InvalidArgumentException('Invalid Container XML dependency key.');
        }
        $kind = array_shift($parts);
        $name = array_shift($parts) ?? '';
        $value = match ($kind) {
            'parameter' => $this->reader->parameter($name, $parts),
            'service' => $this->reader->service($name),
            'serviceClass' => $this->reader->serviceClass($name),
            'listeners' => $this->reader->listeners(),
            'workflows' => $this->reader->workflows(),
            'workflowServices' => $this->reader->workflowServices(),
            default => throw new InvalidArgumentException('Invalid Container XML dependency key.'),
        };

        return hash('sha256', serialize($value));
    }

    public function keyToResultCache(string $key): string
    {
        return $key;
    }

    public function keyFromResultCache(string $storedKey): string
    {
        return $storedKey;
    }
}
