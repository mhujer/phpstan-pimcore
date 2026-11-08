<?php

declare(strict_types=1);

/**
 * Generates Pimcore DataObject classes for the fixture definitions in definitions.php,
 * without a database or a booted kernel, by calling Pimcore's class builders directly.
 *
 * It also writes the class definition files (definition_*.php, objectbricks/*.php,
 * fieldcollections/*.php) in the format Pimcore saves them to var/classes.
 *
 * Usage: php generate.php <vendor-dir> <output-dir> <definitions-output-dir>
 */

use Pimcore\DataObject\ClassBuilder\ClassBuilder;
use Pimcore\DataObject\ClassBuilder\FieldCollectionClassBuilder;
use Pimcore\DataObject\ClassBuilder\FieldDefinitionBuilder;
use Pimcore\DataObject\ClassBuilder\FieldDefinitionDocBlockBuilder;
use Pimcore\DataObject\ClassBuilder\FieldDefinitionPropertiesBuilder;
use Pimcore\DataObject\ClassBuilder\ListingClassBuilder;
use Pimcore\DataObject\ClassBuilder\ListingClassFieldDefinitionBuilder;
use Pimcore\DataObject\ClassBuilder\ObjectBrickClassBuilder;
use Pimcore\DataObject\ClassBuilder\ObjectBrickContainerClassBuilder;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Factory;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Objectbrick;

if ($argc !== 4) {
    fwrite(STDERR, "Usage: php generate.php <vendor-dir> <output-dir> <definitions-output-dir>\n");
    exit(1);
}
[, $vendorDir, $outputDir, $definitionsOutputDir] = $argv;

require $vendorDir . '/autoload.php';

define('PIMCORE_PROJECT_ROOT', __DIR__);
define('PIMCORE_CLASS_DIRECTORY', $outputDir);
// Definition lookups must never hit real files; point them at an empty directory.
define('PIMCORE_CLASS_DEFINITION_DIRECTORY', __DIR__ . '/empty');
define('PIMCORE_CUSTOM_CONFIGURATION_CLASS_DEFINITION_DIRECTORY', __DIR__ . '/empty');

// Relation getters ask the container for the model factory (to map class names to overrides).
// A stub kernel with an empty factory stands in for a booted Pimcore.
$container = new Container();
$container->set('pimcore.model.factory', new Factory([]));
Pimcore::setKernel(new class ($container) extends Kernel {
    public function __construct(private readonly Container $stubContainer)
    {
        parent::__construct('dev', false);
    }

    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    public function getContainer(): Container
    {
        return $this->stubContainer;
    }
});

/** @var array{classes: list<ClassDefinition>, objectbricks: list<Objectbrick\Definition>, fieldcollections: list<Fieldcollection\Definition>} $definitions */
$definitions = require __DIR__ . '/definitions.php';

$docBlockBuilder = new FieldDefinitionDocBlockBuilder();
$classBuilder = new ClassBuilder($docBlockBuilder, new FieldDefinitionPropertiesBuilder(), new FieldDefinitionBuilder());
$listingBuilder = new ListingClassBuilder(new ListingClassFieldDefinitionBuilder());
$brickBuilder = new ObjectBrickClassBuilder($docBlockBuilder);
$brickContainerBuilder = new ObjectBrickContainerClassBuilder();
$fieldCollectionBuilder = new FieldCollectionClassBuilder($docBlockBuilder);

$write = static function (string $relativePath, string $code) use ($outputDir): void {
    $path = $outputDir . '/' . $relativePath;
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    file_put_contents($path, $code);
    echo $relativePath, "\n";
};

$classesByName = [];
foreach ($definitions['classes'] as $class) {
    $classesByName[$class->getName()] = $class;
    $name = ucfirst($class->getName());
    $write("DataObject/{$name}.php", $classBuilder->buildClass($class));
    $write("DataObject/{$name}/Listing.php", $listingBuilder->buildListingClass($class));
}

// Brick containers: one per (class, objectbricks field), listing every brick allowed in that field.
$bricksByContainer = [];
foreach ($definitions['objectbricks'] as $brick) {
    $brickName = ucfirst($brick->getKey());
    $write("DataObject/Objectbrick/Data/{$brickName}.php", $brickBuilder->buildClass($brick));
    foreach ($brick->getClassDefinitions() as $usage) {
        $bricksByContainer[$usage['classname']][$usage['fieldname']][] = $brick;
    }
}
foreach ($bricksByContainer as $className => $fields) {
    foreach ($fields as $fieldName => $bricks) {
        $keys = array_map(static fn (Objectbrick\Definition $b): string => $b->getKey(), $bricks);
        $code = $brickContainerBuilder->buildContainerClass($bricks[0], $classesByName[$className], $fieldName, $keys);
        $write('DataObject/' . ucfirst($className) . '/' . ucfirst($fieldName) . '.php', $code);
    }
}

foreach ($definitions['fieldcollections'] as $collection) {
    $write('DataObject/Fieldcollection/Data/' . ucfirst($collection->getKey()) . '.php', $fieldCollectionBuilder->buildClass($collection));
}

// Class definition files, exported the way the definitions' own save() does it (clone, drop the
// runtime state, var_export). Runs in each definition's class scope to reach protected properties.
$exportDefinition = static function (string $relativePath, object $definition) use ($definitionsOutputDir): void {
    $export = Closure::bind(static function (object $definition): string {
        $clone = DataObject\Service::cloneDefinition($definition);
        $clone->setDao(null);
        if ($clone instanceof ClassDefinition) {
            $clone->setFieldDefinitions([]);
        } else {
            unset($clone->oldClassDefinitions, $clone->fieldDefinitions);
        }
        ClassDefinition::cleanupForExport($clone->layoutDefinitions);

        return "<?php\n\nreturn " . var_export($clone, true) . ";\n";
    }, null, $definition::class);

    $path = $definitionsOutputDir . '/' . $relativePath;
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    file_put_contents($path, $export($definition));
    echo $relativePath, "\n";
};

foreach ($definitions['classes'] as $class) {
    $exportDefinition('definition_' . $class->getName() . '.php', $class);
}
foreach ($definitions['objectbricks'] as $brick) {
    $exportDefinition('objectbricks/' . $brick->getKey() . '.php', $brick);
}
foreach ($definitions['fieldcollections'] as $collection) {
    $exportDefinition('fieldcollections/' . $collection->getKey() . '.php', $collection);
}
