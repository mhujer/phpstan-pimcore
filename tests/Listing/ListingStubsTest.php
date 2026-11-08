<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Listing;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class ListingStubsTest extends TestCase
{
    public function testEveryGeneratedListingHasIntegerKeysAndItsOwnElementType(): void
    {
        $project = self::project();
        self::assertSame(['Dumped type: int', 'Dumped type: Pimcore\\Model\\DataObject\\Product', 'Dumped type: int', 'Dumped type: Pimcore\\Model\\DataObject\\Category'], array_column(Phpstan::analyse($project), 'message'));
    }

    public function testNoDefinitionsKeepTheOriginalListingHierarchy(): void
    {
        $project = self::project();
        foreach (glob($project . '/definitions/*.php') ?: [] as $file) {
            unlink($file);
        }
        self::assertSame(self::originalTypes(), array_column(Phpstan::analyse($project), 'message'));
        self::assertSame([], glob($project . '/cache/pimcore-stubs/*.stub'));
    }

    public function testCustomListingParentDisablesTheEntireChainAndChangesInvalidateCachedFiles(): void
    {
        $project = self::project();
        $before = array_column(Phpstan::analyse($project), 'message');
        self::assertSame('Dumped type: int', $before[0]);
        self::assertSame($before, array_column(Phpstan::analyse($project), 'message'));
        $file = $project . '/definitions/definition_Product.php';
        $definition = (string) file_get_contents($file);
        file_put_contents($file, str_replace("'listingParentClass' => ''", "'listingParentClass' => 'App\\ListingParent'", $definition));
        self::assertSame(self::originalTypes(), array_column(Phpstan::analyse($project), 'message'));
        file_put_contents($file, $definition);
        self::assertSame($before, array_column(Phpstan::analyse($project), 'message'));
    }

    public function testRegisteredOverridesReachPlainListingParametersAndXmlChangesRewriteTheStub(): void
    {
        $project = self::project();
        $config = $project . '/phpstan.neon';
        file_put_contents($config, (string) file_get_contents($config) . "\n        containerXmlPath: overrides.xml\n");
        $xml = (string) file_get_contents(__DIR__ . '/../Type/container-overrides/overrides.xml');
        file_put_contents($project . '/overrides.xml', $xml);
        $expected = ['Dumped type: int', 'Dumped type: App\\Model\\RegisteredProduct', 'Dumped type: int', 'Dumped type: App\\Model\\Category'];
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
        file_put_contents($project . '/overrides.xml', str_replace('App\\Model\\RegisteredProduct', 'App\\Model\\Product', $xml));
        $expected[1] = 'Dumped type: App\\Model\\Product';
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
    }

    public function testOverrideKeysWithALeadingBackslashReachListingsAndLoads(): void
    {
        $project = self::project();
        $config = $project . '/phpstan.neon';
        file_put_contents($config, (string) file_get_contents($config) . "\n        containerXmlPath: overrides.xml\n");
        $xml = (string) file_get_contents(__DIR__ . '/../Type/container-overrides/overrides.xml');
        file_put_contents($project . '/overrides.xml', str_replace('<parameter key="Pimcore\\', '<parameter key="\\Pimcore\\', $xml));
        file_put_contents($project . '/src/check.php', <<<'PHP'
            <?php
            function listing(\Pimcore\Model\DataObject\Product\Listing $products): void {
                foreach ($products as $product) {
                    \PHPStan\dumpType($product);
                }
            }
            \PHPStan\dumpType(\Pimcore\Model\DataObject\Product::getById(1));
            PHP);
        self::assertSame(['Dumped type: App\\Model\\RegisteredProduct', 'Dumped type: App\\Model\\RegisteredProduct|null'], array_column(Phpstan::analyse($project), 'message'));
    }

    public function testUnregisteredFactoryFallbackPreservesIterationAndGeneratedFiltersAcrossCachedRuns(): void
    {
        $project = self::project();
        file_put_contents($project . '/phpstan.neon', (string) file_get_contents($project . '/phpstan.neon') . "\n        containerXmlPath: overrides.xml\n");
        copy(__DIR__ . '/../Type/container-overrides/overrides.xml', $project . '/overrides.xml');
        file_put_contents($project . '/src/check.php', <<<'PHP'
            <?php
            foreach (\App\Model\ChildProduct::getList()->filterBySku('a') as $key => $product) {
                \PHPStan\dumpType($key);
                \PHPStan\dumpType($product);
            }
            foreach (\App\Model\ChildProduct::getBySku('a') as $product) {
                \PHPStan\dumpType($product);
            }
            PHP);
        $expected = ['Dumped type: int', 'Dumped type: Pimcore\\Model\\DataObject\\Product', 'Dumped type: Pimcore\\Model\\DataObject\\Product'];
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
        self::assertSame($expected, array_column(Phpstan::analyse($project), 'message'));
    }

    public function testAddingAndRemovingADefinitionRewritesTheStub(): void
    {
        $project = self::project();
        Phpstan::analyse($project);
        $stubs = glob($project . '/cache/pimcore-stubs/*.stub') ?: [];
        self::assertCount(1, $stubs);
        $before = (string) file_get_contents($stubs[0]);
        $definition = (string) file_get_contents($project . '/definitions/definition_Category.php');
        file_put_contents($project . '/definitions/definition_Gadget.php', str_replace("'name' => 'Category'", "'name' => 'Gadget'", $definition));
        Phpstan::analyse($project);
        $after = (string) file_get_contents($stubs[0]);
        self::assertNotSame($before, $after);
        self::assertStringContainsString('namespace Pimcore\\Model\\DataObject\\Gadget {', $after);
        unlink($project . '/definitions/definition_Gadget.php');
        Phpstan::analyse($project);
        self::assertSame($before, file_get_contents($stubs[0]));
    }

    /** @return list<string> */
    private static function originalTypes(): array
    {
        return ['Dumped type: int|string|null', 'Dumped type: Pimcore\\Model\\DataObject\\Product|false', 'Dumped type: int|string|null', 'Dumped type: Pimcore\\Model\\DataObject\\Category|false'];
    }

    private static function project(): string
    {
        $project = Phpstan::createProject();
        mkdir($project . '/src');
        mkdir($project . '/definitions');
        foreach (glob(Fixtures::definitionsDirectory() . '/definition_*.php') ?: [] as $file) {
            copy($file, $project . '/definitions/' . basename($file));
        }
        file_put_contents($project . '/src/check.php', <<<'PHP'
            <?php
            function listings(\Pimcore\Model\DataObject\Product\Listing $products, \Pimcore\Model\DataObject\Category\Listing $categories): void {
                foreach ($products as $key => $product) {
                    \PHPStan\dumpType($key);
                    \PHPStan\dumpType($product);
                }
                foreach ($categories as $key => $category) {
                    \PHPStan\dumpType($key);
                    \PHPStan\dumpType($category);
                }
            }
            PHP);
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
            parameters:
                level: max
                paths: [src]
                tmpDir: cache
                bootstrapFiles: [%s/tests/bootstrap.php]
                pimcore:
                    classDefinitionDirectory: definitions
            NEON, Phpstan::root(), Phpstan::root()));

        return $project;
    }
}
