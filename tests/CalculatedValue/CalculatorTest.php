<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\CalculatedValue;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    public function testSharedCalculatorAcceptsTheUnionOfEveryOwner(): void
    {
        $project = self::project('Product|Category');
        self::assertSame([], Phpstan::analyse($project, true));
    }

    public function testNarrowingMustCoverEveryOwnerAndLeavesOtherMethodsAndParametersAlone(): void
    {
        $project = self::project('Product');
        $errors = Phpstan::analyse($project, true);
        self::assertSame(['method.childParameterType'], array_column($errors, 'identifier'));
        self::assertStringContainsString('Parameter #1 $object', $errors[0]['message']);

        $project = self::project('Product|Category');
        $file = $project . '/src/calculator.php';
        $source = (string) file_get_contents($file);
        file_put_contents($file, str_replace('compute(', 'getCalculatedValueForEditMode(', $source));
        self::assertSame(['method.childParameterType'], array_column(Phpstan::analyse($project, true), 'identifier'));

        file_put_contents($file, str_replace('/** @param Product|Category $object */', '/** @param Product|Category $object
 * @param SpecialContext $context */', $source) . '
class SpecialContext extends CalculatedValue {}');
        $errors = Phpstan::analyse($project, true);
        self::assertSame(['method.childParameterType'], array_column($errors, 'identifier'));
        self::assertStringContainsString('Parameter #2 $context', $errors[0]['message']);
    }

    public function testLocalizedBrickAndFieldcollectionCalculatorsUseTheOwningConcrete(): void
    {
        foreach (['localizedfields', 'objectbricks', 'fieldcollections'] as $kind) {
            $project = self::project('Product');
            $calculator = "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\CalculatedValue::__set_state(['name' => 'nested', 'calculatorType' => 'class', 'calculatorClass' => 'App\\\\Calculator\\\\SharedCalculator'])";
            if ($kind === 'localizedfields') {
                $field = "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Localizedfields::__set_state(['name' => 'localizedfields', 'children' => [$calculator]])";
            } else {
                $fieldType = $kind === 'objectbricks' ? 'Objectbricks' : 'Fieldcollections';
                $field = "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\$fieldType::__set_state(['name' => 'items', 'allowedTypes' => ['Nested']])";
                self::writeDefinition($project . '/definitions/' . $kind . '/Nested.php', 'Nested', $calculator);
            }
            self::writeDefinition($project . '/definitions/definition_Category.php', 'Category', $field);
            self::assertSame(['method.childParameterType'], array_column(Phpstan::analyse($project, true), 'identifier'), $kind . ' must contribute Category as an owner');
            $file = $project . '/src/calculator.php';
            file_put_contents($file, str_replace('@param Product ', '@param Product|Category ', (string) file_get_contents($file)));
            self::assertSame([], Phpstan::analyse($project, true), $kind . ' passes the owning Concrete, not the item');
        }
    }

    public function testServiceIdsMissingDefinitionsAndUnusedCalculatorsKeepTheirDiagnostics(): void
    {
        foreach (['@shared_calculator', 'App\\Calculator\\UnusedCalculator'] as $reference) {
            $project = self::project('Product|Category');
            foreach (glob($project . '/definitions/definition_*.php') ?: [] as $file) {
                file_put_contents($file, str_replace('App\\\\Calculator\\\\SharedCalculator', str_replace('\\', '\\\\', $reference), (string) file_get_contents($file)));
            }
            self::assertSame(['method.childParameterType'], array_column(Phpstan::analyse($project, true), 'identifier'));
        }
        $project = self::project('Product|Category');
        foreach (glob($project . '/definitions/definition_*.php') ?: [] as $file) {
            unlink($file);
        }
        self::assertSame(['method.childParameterType'], array_column(Phpstan::analyse($project, true), 'identifier'));
    }

    public function testChangedNestedOwnersInvalidateCachedCalculatorDiagnostics(): void
    {
        $project = self::project('Product');
        $categoryFile = $project . '/definitions/definition_Category.php';
        self::writeDefinition($categoryFile, 'Category', "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Fieldcollections::__set_state(['name' => 'entries', 'allowedTypes' => ['StockEntry']])");
        self::assertSame([], Phpstan::analyse($project, true));
        self::assertSame([], Phpstan::analyse($project, true));
        $file = $project . '/definitions/fieldcollections/StockEntry.php';
        $before = (string) file_get_contents($file);
        self::writeDefinition($file, 'StockEntry', "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\CalculatedValue::__set_state(['name' => 'nested', 'calculatorType' => 'class', 'calculatorClass' => 'App\\\\Calculator\\\\SharedCalculator'])");
        self::assertSame(['method.childParameterType'], array_column(Phpstan::analyse($project, true), 'identifier'));
        file_put_contents($file, $before);
        self::assertSame([], Phpstan::analyse($project, true));
    }

    public function testServiceCalculatorGettersAndMissingDefinitionsStayMixed(): void
    {
        $project = self::project('Product|Category');
        file_put_contents($project . '/src/calculator.php', <<<'PHP'
            <?php
            function values(\Pimcore\Model\DataObject\Product $product): void {
                \PHPStan\dumpType($product->getMargin());
                \PHPStan\dumpType($product->getComputedPrice());
            }
            PHP);
        self::assertSame(['Dumped type: string|null', 'Dumped type: mixed'], array_column(Phpstan::analyse($project), 'message'));
        $file = $project . '/definitions/definition_Product.php';
        file_put_contents($file, str_replace('App\\\\Calculator\\\\SharedCalculator', '@shared_calculator', (string) file_get_contents($file)));
        self::assertSame(['Dumped type: mixed', 'Dumped type: mixed'], array_column(Phpstan::analyse($project), 'message'));
        unlink($file);
        self::assertSame(['Dumped type: mixed', 'Dumped type: mixed'], array_column(Phpstan::analyse($project), 'message'));
    }

    public function testCalculatedGettersNarrowOnLocalizedOwnersBrickAndFieldcollectionItems(): void
    {
        $project = self::project('Product|Category');
        $calculator = "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\CalculatedValue::__set_state(['name' => 'calculated', 'calculatorType' => 'class', 'calculatorClass' => 'App\\\\Calculator\\\\SharedCalculator'])";
        self::writeDefinition($project . '/definitions/definition_CalculatedOwner.php', 'CalculatedOwner', "\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Localizedfields::__set_state(['name' => 'localizedfields', 'children' => [$calculator]])");
        self::writeDefinition($project . '/definitions/objectbricks/CalcBrick.php', 'CalcBrick', $calculator);
        self::writeDefinition($project . '/definitions/fieldcollections/CalcEntry.php', 'CalcEntry', $calculator);
        file_put_contents($project . '/src/calculator.php', <<<'PHP'
            <?php
            namespace Pimcore\Model\DataObject {
                abstract class CalculatedOwner extends Concrete {
                    public function getCalculated(?string $language = null): mixed { return null; }
                }
            }
            namespace Pimcore\Model\DataObject\Objectbrick\Data {
                abstract class CalcBrick extends AbstractData {
                    public function getCalculated(): mixed { return null; }
                }
            }
            namespace Pimcore\Model\DataObject\Fieldcollection\Data {
                abstract class CalcEntry extends AbstractData {
                    public function getCalculated(): mixed { return null; }
                }
            }
            namespace {
                function values(
                    \Pimcore\Model\DataObject\CalculatedOwner $owner,
                    \Pimcore\Model\DataObject\Objectbrick\Data\CalcBrick $brick,
                    \Pimcore\Model\DataObject\Fieldcollection\Data\CalcEntry $entry,
                ): void {
                    \PHPStan\dumpType($owner->getCalculated('en'));
                    \PHPStan\dumpType($brick->getCalculated());
                    \PHPStan\dumpType($entry->getCalculated());
                }
            }
            PHP);
        self::assertSame(['Dumped type: string|null', 'Dumped type: string|null', 'Dumped type: string|null'], array_column(Phpstan::analyse($project), 'message'));
    }

    private static function writeDefinition(string $file, string $name, string $field): void
    {
        file_put_contents($file, "<?php return \\Pimcore\\Model\\DataObject\\ClassDefinition::__set_state(['name' => '$name', 'layoutDefinitions' => \\Pimcore\\Model\\DataObject\\ClassDefinition\\Layout\\Panel::__set_state(['children' => [$field]])]);");
    }

    private static function project(string $owners): string
    {
        $project = Phpstan::createProject();
        mkdir($project . '/src');
        mkdir($project . '/definitions');
        foreach (glob(Fixtures::definitionsDirectory() . '/definition_*.php') ?: [] as $file) {
            copy($file, $project . '/definitions/' . basename($file));
        }
        foreach (['objectbricks', 'fieldcollections'] as $kind) {
            mkdir($project . '/definitions/' . $kind);
            foreach (glob(Fixtures::definitionsDirectory() . '/' . $kind . '/*.php') ?: [] as $file) {
                copy($file, $project . '/definitions/' . $kind . '/' . basename($file));
            }
        }
        file_put_contents($project . '/src/calculator.php', str_replace('OWNER_TYPE', $owners, <<<'PHP'
            <?php
            namespace App\Calculator;
            use Pimcore\Model\DataObject\Product;
            use Pimcore\Model\DataObject\Category;
            use Pimcore\Model\DataObject\Concrete;
            use Pimcore\Model\DataObject\Data\CalculatedValue;
            use Pimcore\Model\DataObject\ClassDefinition\CalculatorClassInterface;
            abstract class SharedCalculator implements CalculatorClassInterface {
                /** @param OWNER_TYPE $object */
                public function compute(Concrete $object, CalculatedValue $context): string { return ''; }
            }
            PHP));
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %s/extension.neon
            parameters:
                level: max
                reportMaybesInMethodSignatures: true
                paths: [src]
                tmpDir: cache
                bootstrapFiles: [%s/tests/bootstrap.php]
                pimcore:
                    classDefinitionDirectory: definitions
            NEON, Phpstan::root(), Phpstan::root()));

        return $project;
    }
}
