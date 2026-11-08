<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\ClassDefinition;

use Mhujer\PHPStan\Pimcore\ClassDefinition\ClassDefinitionReader;
use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\ScopeContext;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Parser\Parser;
use PHPStan\Testing\PHPStanTestCase;
use Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections;
use Pimcore\Model\DataObject\ClassDefinition\Data\Select;

final class ClassDefinitionReaderTest extends PHPStanTestCase
{
    // Fixture classes, which this repository's own analysis doesn't see.
    private const string PRODUCT = 'Pimcore\\Model\\DataObject\\Product';
    private const string DIMENSIONS = 'Pimcore\\Model\\DataObject\\Objectbrick\\Data\\Dimensions';
    private const string PRICE_TIER = 'Pimcore\\Model\\DataObject\\Fieldcollection\\Data\\PriceTier';

    public function testReadsAFieldOfADataObjectClass(): void
    {
        $color = self::reader(Fixtures::definitionsDirectory())->forGeneratedClass(self::PRODUCT, self::scope())?->field('color');

        self::assertNotNull($color);
        self::assertSame(Select::class, $color->type);
        self::assertFalse($color->localized);
        self::assertSame([['key' => 'Red', 'value' => 'red'], ['key' => 'Green', 'value' => 'green']], $color->property('options'));
        self::assertNull($color->property('optionsProviderClass'));
    }

    public function testReadsCalculatedValueFixturesOnBothLines(): void
    {
        foreach (['12.3', '2026'] as $line) {
            $reader = self::reader(__DIR__ . '/../fixtures/definitions/' . $line);
            $product = $reader->forGeneratedClass(self::PRODUCT, self::scope());
            $category = $reader->forGeneratedClass('Pimcore\\Model\\DataObject\\Category', self::scope());

            self::assertNotNull($product);
            self::assertNotNull($category);
            $margin = $product->field('margin');
            $label = $category->field('label');
            $expression = $product->field('computedPrice');
            self::assertNotNull($margin);
            self::assertNotNull($label);
            self::assertNotNull($expression);
            self::assertSame('class', $margin->property('calculatorType'));
            self::assertSame('App\\Calculator\\SharedCalculator', $margin->property('calculatorClass'));
            self::assertSame('App\\Calculator\\SharedCalculator', $label->property('calculatorClass'));
            self::assertSame('expression', $expression->property('calculatorType'));
            self::assertSame('object.getPrice() * 2', $expression->property('calculatorExpression'));
            self::assertSame(['PriceTier', 'StockEntry'], $product->field('variants')?->property('allowedTypes'));
        }
    }

    public function testReadsStaticMultiselectOptionsOnBothFixtureLines(): void
    {
        foreach (['12.3', '2026'] as $line) {
            $definition = self::reader(__DIR__ . '/../fixtures/definitions/' . $line)->forGeneratedClass(self::PRODUCT, self::scope());
            self::assertNotNull($definition);

            self::assertSame(['red', 'green'], $definition->field('colors')?->staticOptionValues());
            self::assertNull($definition->field('sizes')?->staticOptionValues());
            self::assertSame(['red', 'green'], $definition->field('color')?->staticOptionValues());
            self::assertNull($definition->field('size')?->staticOptionValues());
        }
    }

    public function testReadsBlockChildrenWithoutExposingThemAsOwnerFields(): void
    {
        foreach (['12.3', '2026'] as $line) {
            $definition = self::reader(__DIR__ . '/../fixtures/definitions/' . $line)->forGeneratedClass(self::PRODUCT, self::scope());
            self::assertNotNull($definition);
            $children = $definition->field('content')?->children();

            self::assertNotNull($children);
            self::assertSame(['title', 'text', 'html', 'amount', 'enabled', 'date', 'datetime', 'link', 'image', 'colors', 'categories', 'relations', 'provider', 'qty', 'color', 'category', 'localizedfields', 'location'], array_keys($children));
            self::assertSame('Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Input', $children['title']->type);
            self::assertFalse($children['title']->localized);
            self::assertTrue($children['qty']->property('integer'));
            self::assertSame(['red', 'green'], $children['color']->staticOptionValues());
            self::assertSame([['classes' => 'Category']], $children['category']->property('classes'));
            self::assertSame('Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\Geopoint', $children['location']->type);
            self::assertTrue($children['localizedfields']->children()['caption']->localized);
            self::assertNull($definition->field('qty'));
            self::assertNull($definition->field('caption'));
            self::assertNull($definition->field('location'));
            self::assertTrue($definition->field('title')?->localized);
            self::assertTrue($definition->field('localizedContent')?->children()['title']->localized);
            self::assertSame([], $definition->field('color')?->children());
        }
    }

    public function testReadsAdvancedRelationMetadataOnBothFixtureLines(): void
    {
        foreach (['12.3', '2026'] as $line) {
            $definition = self::reader(__DIR__ . '/../fixtures/definitions/' . $line)->forGeneratedClass(self::PRODUCT, self::scope());
            self::assertNotNull($definition);
            $objects = $definition->field('accessories');
            self::assertNotNull($objects);
            self::assertSame('Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\AdvancedManyToManyObjectRelation', $objects->type);
            self::assertSame('Product', $objects->property('allowedClassId'));
            self::assertSame([
                ['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1],
                ['key' => 'qty', 'label' => 'Quantity', 'type' => 'number', 'position' => 2],
                ['key' => 'data', 'label' => 'Data', 'type' => 'input', 'position' => 3],
            ], $objects->property('columns'));

            $elements = $definition->field('references');
            self::assertNotNull($elements);
            self::assertSame('Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\AdvancedManyToManyRelation', $elements->type);
            self::assertSame([['classes' => 'Category']], $elements->property('classes'));
            self::assertTrue($elements->property('objectsAllowed'));
            self::assertTrue($elements->property('assetsAllowed'));
            self::assertFalse($elements->property('documentsAllowed'));
            self::assertSame([['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1]], $elements->property('columns'));
        }
    }

    public function testStaticOptionsRespectProvidersExactTypesAndMalformedOptions(): void
    {
        $cases = [
            ['Select', ['options' => [['value' => 'red'], ['value' => 1]]], ['red', '1']],
            ['Multiselect', ['options' => [['value' => '01']]], ['01']],
            ['Select', ['options' => [['value' => 'red']], 'optionsProviderClass' => 'App\\Provider'], null],
            ['Select', ['options' => [['value' => 'red']], 'optionsProviderType' => 'select'], null],
            ['Select', ['options' => [['value' => 'red']], 'optionsProviderType' => 'configure', 'optionsProviderClass' => 'App\\Provider'], ['red']],
            ['Select', ['options' => [['value' => 'red']], 'optionsProviderClass' => ''], ['red']],
            ['Country', ['options' => [['value' => 'red']]], null],
            ['Countrymultiselect', ['options' => [['value' => 'red']]], null],
            ['Select', ['options' => []], null],
            ['Select', [], null],
            ['Multiselect', ['options' => [['value' => 'red'], ['value' => true]]], null],
            ['Select', ['options' => [['key' => 'Missing value']]], null],
        ];
        foreach ($cases as [$type, $properties, $expected]) {
            $directory = self::createTempDirectory();
            $state = var_export(['name' => 'color'] + $properties, true);
            file_put_contents($directory . '/definition_Product.php', '<?php return \\Pimcore\\Model\\DataObject\\ClassDefinition::__set_state([
                "layoutDefinitions" => \\Pimcore\\Model\\DataObject\\ClassDefinition\\Layout\\Panel::__set_state([
                    "children" => [\\Pimcore\\Model\\DataObject\\ClassDefinition\\Data\\' . $type . '::__set_state(' . $state . ')],
                ]),
            ]);');

            $field = self::reader($directory)->forGeneratedClass(self::PRODUCT, self::scope())?->field('color');
            self::assertNotNull($field);
            self::assertSame($expected, $field->staticOptionValues());
        }
    }

    public function testReadsFieldsInsideLocalizedfieldsAsLocalized(): void
    {
        $season = self::reader(Fixtures::definitionsDirectory())->forGeneratedClass(self::PRODUCT, self::scope())?->field('season');

        self::assertNotNull($season);
        self::assertTrue($season->localized);
    }

    public function testReadsObjectbrickAndFieldcollectionDefinitions(): void
    {
        $reader = self::reader(Fixtures::definitionsDirectory());

        self::assertSame(Select::class, $reader->forGeneratedClass(self::DIMENSIONS, self::scope())?->field('unit')?->type);
        self::assertNotNull($reader->forGeneratedClass(self::PRICE_TIER, self::scope())?->field('minQuantity'));
    }

    public function testReadsTheAllowedTypesOfAFieldcollectionsField(): void
    {
        $variants = self::reader(Fixtures::definitionsDirectory())->forGeneratedClass(self::PRODUCT, self::scope())?->field('variants');

        self::assertSame(Fieldcollections::class, $variants?->type);
        self::assertSame(['PriceTier', 'StockEntry'], $variants->property('allowedTypes'));
    }

    public function testUnknownFieldsAndClassesHaveNoDefinition(): void
    {
        $reader = self::reader(Fixtures::definitionsDirectory());

        self::assertNull($reader->forGeneratedClass(self::PRODUCT, self::scope())?->field('noSuchField'));
        self::assertNull($reader->forGeneratedClass('Pimcore\Model\DataObject\NoSuchClass', self::scope()));
        self::assertNull($reader->forGeneratedClass(self::class, self::scope()));
    }

    public function testWithoutAnExplicitDirectoryReadsVarClassesUnderTheWorkingDirectory(): void
    {
        $workingDirectory = self::createTempDirectory();
        mkdir($workingDirectory . '/var');
        symlink(Fixtures::definitionsDirectory(), $workingDirectory . '/var/classes');

        $reader = new ClassDefinitionReader(null, $workingDirectory, self::parser());

        self::assertNotNull($reader->forGeneratedClass(self::PRODUCT, self::scope())?->field('color'));
    }

    public function testWithoutAnExplicitDirectoryAndWithoutVarClassesThereAreNoDefinitions(): void
    {
        $reader = new ClassDefinitionReader(null, self::createTempDirectory(), self::parser());

        self::assertNull($reader->forGeneratedClass(self::PRODUCT, self::scope()));
    }

    public function testDefinitionFilesAreParsedNotExecuted(): void
    {
        $directory = self::createTempDirectory();
        file_put_contents($directory . '/definition_Product.php', <<<'PHP'
            <?php

            throw new \LogicException('The definition file was executed.');

            return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
               'name' => 'Product',
               'layoutDefinitions' => \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
                 'children' => array(
                   0 => \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                     'name' => 'sku',
                  )),
                ),
              )),
            ));
            PHP);

        $definition = self::reader($directory)->forGeneratedClass(self::PRODUCT, self::scope());

        self::assertNotNull($definition?->field('sku'));
    }

    public function testAnUnparsableDefinitionFileIsIgnored(): void
    {
        $directory = self::createTempDirectory();
        file_put_contents($directory . '/definition_Product.php', '<?php return ');

        self::assertNull(self::reader($directory)->forGeneratedClass(self::PRODUCT, self::scope()));
    }

    private static function reader(string $directory): ClassDefinitionReader
    {
        return new ClassDefinitionReader($directory, '/nonexistent', self::parser());
    }

    private static function parser(): Parser
    {
        $parser = self::getContainer()->getService('currentPhpVersionSimpleDirectParser');
        self::assertInstanceOf(Parser::class, $parser);

        return $parser;
    }

    private static function scope(): Scope
    {
        return self::getContainer()->getByType(ScopeFactory::class)->create(ScopeContext::create(__FILE__));
    }

    private static function createTempDirectory(): string
    {
        $directory = sys_get_temp_dir() . '/phpstan-pimcore-tests/' . bin2hex(random_bytes(8));
        mkdir($directory, 0o777, true);

        return $directory;
    }
}
