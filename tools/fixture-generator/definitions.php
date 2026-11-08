<?php

declare(strict_types=1);

/**
 * Fixture class definitions. Edit here, then regenerate (see tests/fixtures/README.md).
 */

use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Layout\Panel;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Objectbrick;

/**
 * @param list<Data|ClassDefinition\Layout> $children
 */
function panel(array $children): Panel
{
    $panel = new Panel();
    $panel->setName('pimcore_root');
    $panel->setChildren($children);

    return $panel;
}

/**
 * @template T of Data
 * @param class-string<T> $type
 * @return T
 */
function field(string $type, string $name): Data
{
    $field = new $type();
    $field->setName($name);
    $field->setTitle(ucfirst($name));

    return $field;
}

$brickAccessories = field(Data\AdvancedManyToManyObjectRelation::class, 'accessories');
$brickAccessories->setColumns([['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1]]);

$collectionReferences = field(Data\AdvancedManyToManyRelation::class, 'references');
$collectionReferences->setObjectsAllowed(true);
$collectionReferences->setAssetsAllowed(false);
$collectionReferences->setDocumentsAllowed(true);
$collectionReferences->setColumns([['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1]]);

$localizedAccessories = field(Data\AdvancedManyToManyObjectRelation::class, 'localizedAccessories');
$localizedAccessories->setAllowedClassId('Product');
$localizedAccessories->setColumns([['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1]]);

$unit = field(Data\Select::class, 'unit');
$unit->setOptions([
    ['key' => 'Millimetres', 'value' => 'mm'],
    ['key' => 'Centimetres', 'value' => 'cm'],
]);

$dimensions = new Objectbrick\Definition();
$dimensions->setKey('Dimensions');
$dimensions->setClassDefinitions([['classname' => 'Product', 'fieldname' => 'attributes']]);
$dimensions->setLayoutDefinitions(panel([
    field(Data\Numeric::class, 'width'),
    field(Data\Numeric::class, 'height'),
    $unit,
    $brickAccessories,
]));

$electrical = new Objectbrick\Definition();
$electrical->setKey('Electrical');
$electrical->setClassDefinitions([['classname' => 'Product', 'fieldname' => 'attributes']]);
$electrical->setLayoutDefinitions(panel([
    field(Data\Numeric::class, 'voltage'),
]));

$priceTier = new Fieldcollection\Definition();
$priceTier->setKey('PriceTier');
$priceTier->setLayoutDefinitions(panel([
    $collectionReferences,
    field(Data\Numeric::class, 'minQuantity'),
    field(Data\Numeric::class, 'price'),
]));

$stockEntry = new Fieldcollection\Definition();
$stockEntry->setKey('StockEntry');
$stockEntry->setLayoutDefinitions(panel([
    field(Data\Input::class, 'warehouse'),
    field(Data\Numeric::class, 'quantity'),
]));

// Objectbricks/Fieldcollections::setAllowedTypes() look definitions up by key (normally from
// var/classes/*). Registering them in the runtime cache avoids needing those files.
foreach ([$dimensions, $electrical] as $brick) {
    RuntimeCache::set('objectbrick_' . $brick->getKey(), $brick);
}
foreach ([$priceTier, $stockEntry] as $collection) {
    RuntimeCache::set('fieldcollection_' . $collection->getKey(), $collection);
}

$margin = field(Data\CalculatedValue::class, 'margin');
$margin->setCalculatorType('class');
$margin->setCalculatorClass('App\\Calculator\\SharedCalculator');

$computedPrice = field(Data\CalculatedValue::class, 'computedPrice');
$computedPrice->setCalculatorType('expression');
$computedPrice->setCalculatorExpression('object.getPrice() * 2');

$category = new ClassDefinition();
$category->setId('category');
$category->setName('Category');
$category->setLayoutDefinitions(panel([
    field(Data\Input::class, 'name'),
    field(Data\Input::class, 'code'),
    (clone $margin)->setName('label'),
]));

$season = field(Data\Select::class, 'season');
$season->setOptions([
    ['key' => 'Summer', 'value' => 'summer'],
    ['key' => 'Winter', 'value' => 'winter'],
]);

$localizedContent = field(Data\Block::class, 'localizedContent');
$localizedContent->setChildren([
    field(Data\Input::class, 'title'),
]);

$localized = field(Data\Localizedfields::class, 'localizedfields');
$localized->setChildren([
    field(Data\Input::class, 'title'),
    field(Data\Textarea::class, 'description'),
    $season,
    $localizedContent,
    $localizedAccessories,
]);

$color = field(Data\Select::class, 'color');
$color->setOptions([
    ['key' => 'Red', 'value' => 'red'],
    ['key' => 'Green', 'value' => 'green'],
]);

// An options provider decides the options at runtime; the configured ones are stale.
$size = field(Data\Select::class, 'size');
$size->setOptions([
    ['key' => 'Small', 'value' => 's'],
]);
$size->setOptionsProviderType(Data\OptionsProviderInterface::TYPE_CLASS);
$size->setOptionsProviderClass('App\\OptionsProvider\\SizeOptionsProvider');

$colors = field(Data\Multiselect::class, 'colors');
$colors->setOptions([
    ['key' => 'Red', 'value' => 'red'],
    ['key' => 'Green', 'value' => 'green'],
]);

$sizes = field(Data\Multiselect::class, 'sizes');
$sizes->setOptions([
    ['key' => 'Small', 'value' => 's'],
]);
$sizes->setOptionsProviderType(Data\OptionsProviderInterface::TYPE_CLASS);
$sizes->setOptionsProviderClass('App\\OptionsProvider\\SizeOptionsProvider');

$mainCategory = field(Data\ManyToOneRelation::class, 'mainCategory');
$mainCategory->setObjectsAllowed(true);
$mainCategory->setClasses([['classes' => 'Category']]);

$categories = field(Data\ManyToManyObjectRelation::class, 'categories');
$categories->setClasses([['classes' => 'Category']]);

$related = field(Data\ManyToManyRelation::class, 'related');
$related->setObjectsAllowed(true);
$related->setAssetsAllowed(true);
$related->setDocumentsAllowed(true);

$accessories = field(Data\AdvancedManyToManyObjectRelation::class, 'accessories');
$accessories->setAllowedClassId('Product');
$accessories->setColumns([
    ['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1],
    ['key' => 'qty', 'label' => 'Quantity', 'type' => 'number', 'position' => 2],
    ['key' => 'data', 'label' => 'Data', 'type' => 'input', 'position' => 3],
]);

$references = field(Data\AdvancedManyToManyRelation::class, 'references');
$references->setObjectsAllowed(true);
$references->setAssetsAllowed(true);
$references->setDocumentsAllowed(false);
$references->setClasses([['classes' => 'Category']]);
$references->setColumns([
    ['key' => 'note', 'label' => 'Note', 'type' => 'input', 'position' => 1],
]);

$contentCategory = field(Data\ManyToOneRelation::class, 'category');
$contentCategory->setObjectsAllowed(true);
$contentCategory->setClasses([['classes' => 'Category']]);

$contentColor = field(Data\Select::class, 'color');
$contentColor->setOptions([
    ['key' => 'Red', 'value' => 'red'],
    ['key' => 'Green', 'value' => 'green'],
]);

$contentQuantity = field(Data\Numeric::class, 'qty');
$contentQuantity->setInteger(true);

$contentLocalized = field(Data\Localizedfields::class, 'localizedfields');
$contentLocalized->setChildren([
    field(Data\Input::class, 'caption'),
]);

$content = field(Data\Block::class, 'content');
$content->setChildren([
    field(Data\Input::class, 'title'),
    field(Data\Textarea::class, 'text'),
    field(Data\Wysiwyg::class, 'html'),
    field(Data\Numeric::class, 'amount'),
    field(Data\Checkbox::class, 'enabled'),
    field(Data\Date::class, 'date'),
    field(Data\Datetime::class, 'datetime'),
    field(Data\Link::class, 'link'),
    field(Data\Image::class, 'image'),
    (clone $colors),
    (clone $categories),
    (clone $related)->setName('relations'),
    (clone $size)->setName('provider'),
    $contentQuantity,
    $contentColor,
    $contentCategory,
    $contentLocalized,
    // A valid block child outside the v0.2 type table keeps mixed data.
    field(Data\Geopoint::class, 'location'),
]);

// Exercise block getters on each supported generated owner.
$ownerContent = (clone $localizedContent)->setName('content');
$dimensions->setLayoutDefinitions(panel([...$dimensions->getLayoutDefinitions()->getChildren(), clone $ownerContent]));
$priceTier->setLayoutDefinitions(panel([...$priceTier->getLayoutDefinitions()->getChildren(), clone $ownerContent]));

$attributes = field(Data\Objectbricks::class, 'attributes');
$attributes->setAllowedTypes(['Dimensions', 'Electrical']);

$variants = field(Data\Fieldcollections::class, 'variants');
$variants->setAllowedTypes(['PriceTier', 'StockEntry']);

$product = new ClassDefinition();
$product->setId('product');
$product->setName('Product');
$product->setLayoutDefinitions(panel([
    field(Data\Input::class, 'sku'),
    field(Data\Input::class, 'name'),
    field(Data\Numeric::class, 'price'),
    field(Data\Checkbox::class, 'active'),
    field(Data\Date::class, 'releaseDate'),
    field(Data\Image::class, 'image'),
    $localized,
    $color,
    $size,
    $colors,
    $sizes,
    $mainCategory,
    $categories,
    $related,
    $accessories,
    $references,
    $content,
    $attributes,
    $variants,
    // Pimcore generates no property for a calculated value, only a getter and a field name constant.
    $margin,
    $computedPrice,
]));

return [
    'classes' => [$category, $product],
    'objectbricks' => [$dimensions, $electrical],
    'fieldcollections' => [$priceTier, $stockEntry],
];
