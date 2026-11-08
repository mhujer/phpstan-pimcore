<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

/**
 * A finder's `$limit` takes what Pimcore accepts at runtime: besides an int or null, a bool, a numeric string or the
 * array form. Runs PHPStan itself, at the highest level, since argument types are checked by PHPStan's own rules.
 */
final class FinderArgumentsTest extends TestCase
{
    public function testTheLimitAcceptsWhatPimcoreAcceptsAndOnlyDeclaredFindersExist(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %1$s/extension.neon
            parameters:
                level: max
                paths:
                    - %1$s/tests/Type/calls/finder-limits.php
                tmpDir: cache
                bootstrapFiles:
                    - %1$s/tests/bootstrap.php
            NEON, Phpstan::root()));

        $expects = 'array<string, mixed>|bool|int|numeric-string|null';
        self::assertSame([
            ['line' => 28, 'message' => sprintf('Parameter #2 $limit of static method Pimcore\Model\DataObject\Product::getBySku() expects %s, \'abc\' given.', $expects)],
            ['line' => 29, 'message' => sprintf('Parameter #2 $limit of static method Pimcore\Model\DataObject\Product::getBySku() expects %s, stdClass given.', $expects)],
            ['line' => 34, 'message' => 'Call to an undefined static method Pimcore\Model\DataObject\Product::getByNosuch().'],
            ['line' => 35, 'message' => 'Call to an undefined static method Pimcore\Model\DataObject\Product::getbysku().'],
        ], Phpstan::analyse($project));
    }
}
