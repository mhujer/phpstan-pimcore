<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ClassDefinition;

/**
 * Where the class definitions live, from `pimcore.classDefinitionDirectory`.
 */
final class ClassDefinitionDirectory
{
    private const string DEFAULT_RELATIVE_PATH = 'var/classes';

    private function __construct()
    {
    }

    /**
     * Schema assertion for the parameter: null (the default) or a directory that exists.
     */
    public static function isValidParameter(mixed $value): bool
    {
        return $value === null || (is_string($value) && is_dir($value));
    }

    /**
     * An explicit directory is used as is (the schema made sure it exists). The default `var/classes` may not exist;
     * then no definition file is found, which is the silent fallback.
     */
    public static function resolve(?string $configured, string $currentWorkingDirectory): string
    {
        return $configured ?? $currentWorkingDirectory . '/' . self::DEFAULT_RELATIVE_PATH;
    }
}
