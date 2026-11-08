<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests;

use PHPUnit\Framework\Assert;

/**
 * Runs PHPStan itself, as a user would, on a project directory that has a `phpstan.neon`.
 */
final class Phpstan
{
    private const string ROOT = __DIR__ . '/..';

    private function __construct()
    {
    }

    /**
     * A fresh project directory under the system temp directory.
     */
    public static function createProject(): string
    {
        $project = sys_get_temp_dir() . '/phpstan-pimcore-tests/' . bin2hex(random_bytes(8));
        mkdir($project, 0o777, true);

        return $project;
    }

    /**
     * The absolute path of this repository, e.g. to include `extension.neon` from a project's config.
     */
    public static function root(): string
    {
        return (string) realpath(self::ROOT);
    }

    /**
     * @return list<array{line: int|null, message: string, identifier?: string}> the errors, in PHPStan's order
     * @phpstan-impure
     */
    public static function analyse(string $project, bool $includeIdentifiers = false): array
    {
        $command = sprintf(
            'cd %s && %s %s analyse --configuration phpstan.neon --error-format json --no-progress 2>/dev/null',
            escapeshellarg($project),
            escapeshellarg(PHP_BINARY),
            escapeshellarg(self::root() . '/vendor/bin/phpstan'),
        );
        exec($command, $output);

        $result = json_decode(implode("\n", $output), true);
        Assert::assertIsArray($result, implode("\n", $output));
        Assert::assertIsArray($result['files'] ?? null, implode("\n", $output));

        $errors = [];
        foreach ($result['files'] as $file) {
            Assert::assertIsArray($file);
            Assert::assertIsArray($file['messages']);
            foreach ($file['messages'] as $message) {
                Assert::assertIsArray($message);
                Assert::assertIsString($message['message']);
                $line = $message['line'] ?? null;
                Assert::assertTrue($line === null || is_int($line));
                $error = ['line' => $line, 'message' => $message['message']];
                if ($includeIdentifiers) {
                    Assert::assertIsString($message['identifier'] ?? null);
                    $error['identifier'] = $message['identifier'];
                }

                $errors[] = $error;
            }
        }

        return $errors;
    }
}
