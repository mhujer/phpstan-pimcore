<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ClassDefinition;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

/**
 * Runs PHPStan itself, twice, with the result cache: editing a class definition between the runs must re-analyse the
 * files whose types depend on it.
 */
final class ResultCacheTest extends TestCase
{
    public function testEditingAClassDefinitionInvalidatesTheResultCache(): void
    {
        $project = Phpstan::createProject();
        mkdir($project . '/definitions');
        // PHPStan only uses the result cache for analysed directories, not single files.
        mkdir($project . '/src');
        foreach (glob(Fixtures::definitionsDirectory() . '/definition_*.php') ?: [] as $definition) {
            copy($definition, $project . '/definitions/' . basename($definition));
        }

        file_put_contents($project . '/src/analysed.php', <<<'PHP'
            <?php

            function color(\Pimcore\Model\DataObject\Product $p): void
            {
                \PHPStan\dumpType($p->getColor());
            }
            PHP);
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
            parameters:
                level: 0
                paths:
                    - src
                tmpDir: cache
                bootstrapFiles:
                    - %s/tests/bootstrap.php
                pimcore:
                    classDefinitionDirectory: definitions
            NEON, Phpstan::root(), Phpstan::root()));

        self::assertSame(["Dumped type: ''|'green'|'red'|null"], self::analyse($project));
        // A second run without changes comes from the result cache.
        self::assertSame(["Dumped type: ''|'green'|'red'|null"], self::analyse($project));

        $definition = $project . '/definitions/definition_Product.php';
        file_put_contents($definition, str_replace("'value' => 'green'", "'value' => 'blue'", (string) file_get_contents($definition)));

        self::assertSame(["Dumped type: ''|'blue'|'red'|null"], self::analyse($project));
    }

    /**
     * @return list<string>
     * @phpstan-impure
     */
    private static function analyse(string $project): array
    {
        return array_column(Phpstan::analyse($project), 'message');
    }
}
