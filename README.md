# PHPStan Pimcore extensions and rules

[![Build](https://github.com/mhujer/phpstan-pimcore/actions/workflows/ci.yaml/badge.svg)](https://github.com/mhujer/phpstan-pimcore/actions/workflows/ci.yaml)
[![Latest Stable Version](https://img.shields.io/packagist/v/mhujer/phpstan-pimcore.svg)](https://packagist.org/packages/mhujer/phpstan-pimcore)
[![License](https://img.shields.io/packagist/l/mhujer/phpstan-pimcore.svg)](https://packagist.org/packages/mhujer/phpstan-pimcore)

A PHPStan extension that knows the types and pitfalls of your Pimcore project.

* [PHPStan](https://phpstan.org/)
* [Pimcore](https://pimcore.com/)

This extension provides the following features:

* Correct return types for [finders](#finders), depending on `limit`.
* [Class overrides](#class-overrides) registered in the container, in loads, finders and Listings.
* [Listing](#listings) results as typed lists, and typed Listing iteration.
* `get()`, `getValueForFieldName()` and `getLocalizedValue()` [typed as the field's getter](#generic-getters).
* Callback return types for [`useInheritedValues()` and `withCoauthor()`](#scoped-callbacks).
* Typed [element lookups](#element-lookup), [folder creation](#folder-creation), [copies](#copies) and [element subtypes](#element-subtypes).
* Typed [objectbricks](#objectbricks), [select fields](#select-fields), [fieldcollections](#fieldcollections), [calculated values](#calculated-values), [relation metadata](#relation-metadata) and [blocks](#blocks).

It also contains these rules:

* [Toggles](#toggle-not-restored) such as `setHideUnpublished()` that aren't restored in a `finally`.
* Unknown [field names](#unknown-field-name), [definition names](#unknown-definition-names) and [values keys](#unknown-values-keys).
* Invalid [select options](#select-options), [languages](#language-arguments), [element types, keys and lookup options](#element-lookup-arguments).
* [SQL injection in Listing conditions](#interpolated-listing-condition), [placeholder mistakes](#condition-placeholders) and [invalid Listing arguments](#listing-arguments).
* Invalid [cache keys](#cache-keys) and mismatched load/save keys.
* Bypassed and broken [class override registrations](#class-override-registration).
* [Event listeners](#event-listener-wiring) whose parameter doesn't match the dispatched event.
* Unknown [workflow, transition and place names](#workflow-names).

## Requirements

Pimcore 12.3 (platform 2025.4) or 2026.3+, PHP 8.3 or newer, PHPStan 2.3 or newer.

## Reporting false positives

The extension is new and there may be false positives. Start from a clean PHPStan report, so that everything reported after installing comes from the extension:

1. Before installing, make `vendor/bin/phpstan analyse` report no errors: fix them, or move them to a [baseline](https://phpstan.org/user-guide/baseline) with `--generate-baseline`.
2. Install the extension and run PHPStan again.
3. Ask your coding agent (Claude Code, Codex, Cursor, …) to check the new errors, with the prompt below.
4. Read the reports, and paste each into a [new issue](https://github.com/mhujer/phpstan-pimcore/issues/new?template=false-positive.yml).

<details>
<summary>Prompt</summary>

```text
I installed the mhujer/phpstan-pimcore PHPStan extension. Before that, PHPStan reported no errors, so every error now comes from the extension (including errors without a `pimcore.` identifier, which can come from types it infers). Run `vendor/bin/phpstan analyse`.

For each error, read the code and the Pimcore classes it touches, and decide whether it's a valid report or a false positive: is the code correct at runtime? List the valid ones with your reasoning.

For each false positive, write a bug report for the extension's maintainer, who can't see this project:
1. Collect the versions of mhujer/phpstan-pimcore, phpstan/phpstan and pimcore/pimcore from composer.lock, and the PHP version. Copy the `pimcore` parameters from the PHPStan config, and note whether class definitions (var/classes) and a Container XML are in use.
2. Build the smallest reproduction: a self-contained PHP snippet that triggers the same error, plus only the parts of the class definition (var/classes/definition_*.php) and the generated class (var/classes/DataObject/*.php) that it needs. Leave out unrelated fields and methods. If the Container XML is involved, quote only the elements that matter.
3. If you can, check the reproduction: put it in a temporary file, run PHPStan on it and confirm the same error, then delete the file. Leave the project as it was.
4. Anonymise everything: replace project, class, field and other business names with neutral ones (Product, Category, name, …). Never include secrets, credentials, hostnames or other values from the Container XML or .env files.

Write each report in Markdown with these sections: Error, Why it's a false positive, Versions, Configuration, Reproduction, Expected (what PHPStan should report instead: nothing, or the correct type).

Don't change any code and don't open issues yourself. Output the reports so I can copy each into an issue.
```

</details>

## Installation

```bash
composer require --dev mhujer/phpstan-pimcore
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer), then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include both files in your `phpstan.neon`. `rules.neon` depends on the parameters declared in `extension.neon`, so it can't be used alone:

```neon
includes:
    - vendor/mhujer/phpstan-pimcore/extension.neon
    - vendor/mhujer/phpstan-pimcore/rules.neon
```

If you only want the type inference and none of the rules, include just `extension.neon`. When you use the extension-installer, tell it to skip this package so it doesn't load `rules.neon`:

```json
{
    "extra": {
        "phpstan/extension-installer": {
            "ignore": ["mhujer/phpstan-pimcore"]
        }
    }
}
```

```neon
includes:
    - vendor/mhujer/phpstan-pimcore/extension.neon
```

</details>

## Configuration

These are the defaults:

```neon
parameters:
    pimcore:
        classDefinitionDirectory: null
        containerXmlPath: null
        additionalToggles: []
```

`classDefinitionDirectory` is where your class definitions are: the `definition_*.php` files, plus the `objectbricks/` and `fieldcollections/` subdirectories, as Pimcore writes them to `var/classes`. With `null`, the extension looks in `var/classes` under the current working directory and uses it if it exists; otherwise it does without. A directory you set must exist, or PHPStan stops with a config error. Relative paths are resolved against the config file. Only this one directory is read: definitions Pimcore keeps in its second location, `config/pimcore/classes`, aren't.

The definitions are optional. Your generated classes are the main source of types, and a definition only narrows a getter the generated class already has (see [Select fields](#select-fields)). The files are parsed, never executed, and PHPStan's result cache notices when you change one.

`additionalToggles` adds your own process-wide switches to the built-in ones checked by the [toggle rule](#toggle-not-restored), which has an example.

### Container XML

The optional Container XML supplies compiled Pimcore configuration. Set its path explicitly:

```neon
parameters:
    pimcore:
        containerXmlPath: var/cache/dev/App_KernelDevDebugContainer.xml
```

The default is `null`: the extension borrows `symfony.containerXmlPath` when available, otherwise it works without an XML. Your own path takes precedence, and relative paths are resolved against your PHPStan config file. There is no dependency on phpstan-symfony.

An explicit path must exist and contain a readable Pimcore Container XML, or analysis stops with an error naming its path. A broken borrowed path quietly disables config features. A missing config key disables only the feature that needs it.

Pimcore normally writes `var/cache/dev/App_KernelDevDebugContainer.xml` when warming its debug cache. Warm it before PHPStan, including in CI, so analysis sees current configuration:

```bash
APP_ENV=dev APP_DEBUG=1 php bin/console cache:warmup
vendor/bin/phpstan analyse
```

The file contains secrets in plain text. Keep it out of version control and public CI artifacts. The extension parses it without booting Pimcore or executing project code; it never prints config values. Location-aware config may also live in the database, so the XML alone does not generally prove an entry is missing.

Run `vendor/bin/phpstan diagnose` (or analyse with `-vvv`) to see the XML path, its source (`own`, `symfony` or `none`), and why a borrowed path was disabled. Features track the parameters and subtrees they read in PHPStan's result cache.

### Strictest setup

Load both files (the extension-installer does this for you), supply class definitions, and add a current Container XML:

```neon
includes:
    - vendor/mhujer/phpstan-pimcore/extension.neon
    - vendor/mhujer/phpstan-pimcore/rules.neon

parameters:
    pimcore:
        classDefinitionDirectory: var/classes
        containerXmlPath: var/cache/dev/App_KernelDevDebugContainer.xml
```

Class definitions enable [select field literals](#select-fields), the [select option rule](#select-options), [relation metadata columns and element types](#relation-metadata), and [block item types](#blocks).

The XML enables [registered class override typing](#class-overrides) and [registration checks](#class-override-registration), [language argument checks](#language-arguments), [YAML/XML listener checks](#event-listener-wiring), [Document and Asset subtype literals](#element-subtypes), and [workflow name checks](#workflow-names). Language checks stay silent when the system settings read target is `settings-store`, `disabled` or an unresolved env placeholder, as the XML then can't prove a language is missing. Warm the debug cache before analysis, as shown above.

## Type inference

### Finders

Pimcore returns the object only when `limit` equals 1 (loosely, so `'1'` and `true` count too). Any other limit, or none, gives you the Listing. The extension reads the limit from the call, so you don't have to narrow the union yourself:

```php
Product::getBySku('a');                  // Product\Listing
Product::getBySku('a', 10);              // Product\Listing
Product::getBySku('a', 1);               // Product|null
Product::getBySku('a', ['limit' => 1]);  // Product|null
Product::getBySku('a', limit: 1);        // Product|null
Product::getByTitle('a', 'en', 1);       // Product|null
Product::getBySku('a', $limit);          // Product|Product\Listing|null
```

When the limit isn't a known value, as in the last line, you get Pimcore's declared type. Pimcore declares `$limit` as `?int`, but it also takes a bool, a numeric string or the array form, so the extension accepts those as arguments too.

### Class overrides

With a [Container XML](#container-xml), the extension reads `pimcore.models.class_overrides`. Static loads, finders and Listings use the registered class:

```php
// Registered: Pimcore\Model\DataObject\Product => App\Model\Product
Product::getById(1);                    // App\Model\Product|null
Product::getByPath('/product');         // App\Model\Product|null
Product::getBySku('a', 1);               // App\Model\Product|null
$list = Product::getList();
$list->setLimit(10)->load();             // list<App\Model\Product>
$list->current();                       // App\Model\Product|false
foreach ($list as $product) {
    // $product: App\Model\Product
}
new Product();                         // Pimcore\Model\DataObject\Product
Product::create();                     // Pimcore\Model\DataObject\Product
```

<details>
<summary>Details</summary>

Calls on the registered override or an intermediate ancestor use the same registered class, including an abstract intermediate class. A Listing override becomes the class returned by `getList()` and finders. `load()`, `getData()`, `getObjects()`, `getItems()` and chained setters retain the element class. Object relation getters on any generated class carry the related class's override, including advanced relation metadata when class definitions are available.

An unregistered subclass, including a subclass below the registered override, has `null` single results; its Listings carry the generated class. Missing classes or entries that do not extend their key fall back to default typing. Document and Asset overrides affect static `getById()` and `getByPath()` calls on the exact overridden class; base `Document` and `Asset` calls keep their existing types.

Redeclared finders and `getList()` methods (native or `@method`), a `getList()` config with a `class` key, and abstract called classes outside the intermediate case keep their default types. `new` and `create()` keep the called class.

Without a Container XML, registration is assumed: a subclass's finders and `getList()` carry the called subclass, including sub-subclasses, `self::`, `static::` and class-string calls. `getById()` and `getByPath()` keep Pimcore's `?static`. Relation getters and Listings known only by their generated class keep their generated element types. No registration checks run.

</details>

### Listings

A Listing builds its result arrays itself, so they're lists. `getItems()`, the paginator method, gets the object class too:

```php
$list = Product::getList();
$list->load();              // list<Product>
$list->getData();           // list<Product>
$list->getObjects();        // list<Product>
$list->getItems(0, 10);     // list<Product>
$list->loadIdList();        // list<int>
```

With [class definitions](#configuration), `foreach` over a generated Listing gives an `int` key and the generated class as its value. You can also annotate a general Listing as `Listing\Concrete<Product>`:

```php
foreach ($list as $key => $product) {
    // $key: int, $product: Product
}

/** @param \Pimcore\Model\DataObject\Listing\Concrete<Product> $products */
function processProducts(\Pimcore\Model\DataObject\Listing\Concrete $products): void
{
    // Iteration and load() know Product.
}
```

<details>
<summary>Details</summary>

With a [Container XML](#container-xml), a generated Listing parameter carries its registered element override too. Direct `current()` calls still include `false`, as they can run past the end. The extension generates the stubs in PHPStan's `tmpDir` automatically and tracks definition and override changes for the result cache.

The generic Listing chain is disabled when there are no class definitions or any definition sets a non-empty `listingParentClass`. In that case, iteration keeps Pimcore's existing types.

</details>

### Generic getters

`get('sku')` calls `getSku()`, so it returns what the getter returns. Like Pimcore's method lookup, the field name is case-insensitive. This works on generated DataObject classes, brick containers, brick items and fieldcollection items:

```php
$product->get('sku');                    // string|null
$product->get('title', 'en');            // string|null
$product->get(Product::FIELD_SKU);       // string|null
$dimensions->get('width');               // float|null
```

`getValueForFieldName('sku')` reads the property `$sku`, which is case-sensitive, and is typed as its getter. A localized field has no property of its own, so for it Pimcore always returns `null`:

```php
$product->getValueForFieldName('sku');   // string|null
$product->getValueForFieldName('title'); // null
```

`getLocalizedfields()` returns `Localizedfield<Product>`, so `getLocalizedValue()` is typed as the localized field's getter, too. The type argument is optional: a bare `Localizedfield` in your own signatures still works and needs no change.

```php
$product->getLocalizedfields();                       // Localizedfield<Product>|null
$product->getLocalizedfields()?->getLocalizedValue('title', 'en'); // string|null
```

A union of constant names gives the union of the getter types. When the name isn't a constant, or doesn't match a getter, or the object is only known as `Concrete` or `AbstractObject` (or a `Localizedfield` with no owner), you get `mixed`.

### Scoped callbacks

`DataObject\Service::useInheritedValues()` returns the callback's return type, including calls through a Service subclass. On Pimcore 2026, the same applies to `Version\CoauthorContextInterface::withCoauthor()` and its implementations.

```php
Service::useInheritedValues(true, fn () => $product->getSku()); // string|null
Service::useInheritedValues(true, fn (string $sku) => $sku, ['ABC']); // string
```

<details>
<summary>Details</summary>

Closures, arrow functions, callable arrays, function names, first-class callables and invokable objects are supported, including named arguments. A `void` callback returns `null`; a `never` callback makes the following code unreachable. An unknown callback return type stays `mixed`. The return types of multiple callable variants are combined.

The extension leaves Pimcore's signatures unchanged and does not check `$fnArgs` against callback parameters. Generic callbacks resolve their templates without `$fnArgs`. No class definitions or configuration are needed.

</details>

### Element lookup

`Element\Service::getElementById()` and `getElementByPath()` narrow their return type from a constant element type:

```php
Element\Service::getElementById('asset', $id); // Asset|null
Element\Service::getElementByPath('object', $path); // DataObject|null
```

`'asset'`, `'document'` and `'object'` return `Asset|null`, `Document|null` and `DataObject|null`. A union of constant types gives the union of the results; an invalid constant returns `null`. A non-constant type keeps Pimcore's declared return type. No class definitions are needed.

`getElementType()` and `getElementTypeByClassName()` return the element type literals, preserving the declared nullability:

```php
Element\Service::getElementType($element); // 'asset'|'document'|'object'|null
Element\Service::getElementTypeByClassName($class); // 'asset'|'document'|'object'|null
```

Methods redeclared by a subclass keep their declared return type.

### Folder creation

`createFolderByPath()` returns the called family's folder type:

```php
Asset\Service::createFolderByPath('/images');        // Asset\Folder|null
DataObject\Service::createFolderByPath('/products'); // DataObject\Folder|null
Document\Service::createFolderByPath('/pages');      // Document\Folder|null
```

`null` stays because an existing path can hold a non-folder. No class definitions or configuration are needed.

### Copies

`Element\Service::cloneMe()` keeps the element's class, and `DataObject\Service::copyAsChild()` keeps the source object's class:

```php
Element\Service::cloneMe($product);             // Product
$service->copyAsChild($targetFolder, $product); // Product
```

Class overrides and unions are preserved. No class definitions or configuration are needed. Methods redeclared by a subclass keep their declared return type.

### Element subtypes

`DataObject\AbstractObject::getType()` returns `'object'|'variant'|'folder'`. `User\AbstractUser::getType()` returns `'user'|'userfolder'|'role'|'rolefolder'`. These literals also apply to inherited calls on generated classes, folders, users and roles. No class definitions or configuration are needed. Methods redeclared by a subclass keep their declared return type.

```php
$type = $object->getType(); // 'folder'|'object'|'variant'
if ($type === 'folder') {
    // $type is 'folder'; $object keeps its original class.
}
```

With a [Container XML](#container-xml), `Document::getType()` and `Asset::getType()` return the subtype literals from `pimcore.documents.classes.map` and `pimcore.assets.classes.map`. Calls on a known mapped class use its matching literals, including registered class overrides. Methods redeclared by a subclass keep their declared return type.

```php
$document->getType(); // 'email'|'folder'|'hardlink'|'link'|'page'|'snippet' with the default map
$page->getType();     // 'page'
$image->getType();    // 'image'
```

<details>
<summary>Details</summary>

Comparisons and `match` narrow the returned string, but do not narrow the receiver to a subtype class. PHPStan's public method type-specifying extension API receives a truthiness context, without the compared literal, so this feature currently supplies literal returns only. Without a Container XML type map, Document and Asset calls keep their declared return types. Base `Document::getById()` keeps returning `Document|null`.

</details>

### Objectbricks

The getter of an objectbrick field creates the brick container on first access, so it isn't nullable. On the container, `get('Dimensions')` returns that brick, like `getDimensions()`, and `getItems()` lists the bricks the container allows. Everything comes from the generated container; no class definitions are needed:

```php
$attributes = $product->getAttributes(); // Product\Attributes
$attributes->getDimensions();            // Objectbrick\Data\Dimensions|null
$attributes->get('Dimensions');          // Objectbrick\Data\Dimensions|null
$attributes->getItems();                 // list<Objectbrick\Data\Dimensions|Objectbrick\Data\Electrical>
```

When the brick name isn't a constant, or the container is only known as `Objectbrick`, you get Pimcore's declared types (`mixed` and `array`).

### Select fields

A select with options configured in the class definition returns one of the option values, an empty string, or null. Pimcore can persist an empty string even for mandatory fields when saving drafts. This needs the class definitions (see [Configuration](#configuration)); without them you get the getter's declared type:

```php
// options: red, green
$product->getColor();                    // ''|'green'|'red'|null
$product->get('color');                  // ''|'green'|'red'|null
$product->getColors();                   // array<'green'|'red'>|null
$dimensions->getUnit();                  // ''|'cm'|'mm'|null
```

<details>
<summary>Details</summary>

Multiselect getters return arrays of the static option values, with arbitrary keys and an empty array allowed. Selects and multiselects in localized fields, objectbricks and fieldcollections are narrowed the same way. A select with an options provider stays `string|null`, since the provider decides the options at runtime. If a definition doesn't match the generated class, for example a stale field that is no longer a select, the generated class wins.

</details>

### Fieldcollections

`foreach` over a fieldcollection gives you its items, with an `int` key. The getter names the item classes on both Pimcore lines. Pimcore 2026.x generates that type itself; on 12.3 the extension reads the field's allowed types from the class definition:

```php
$variants = $product->getVariants();     // Fieldcollection<PriceTier|StockEntry>|null
$variants?->getItems();                  // array<PriceTier|StockEntry>
foreach ($variants ?? [] as $i => $item) {
    // $i: int, $item: PriceTier|StockEntry
}
```

Without class definitions, a 12.3 getter returns `Fieldcollection<Fieldcollection\Data\AbstractData>|null`. `getItems()` stays an `array`, not a `list`, since `setItems()` accepts any array.

`add()` and `setItems()` accept only the collection's item types. Passing a disallowed item produces PHPStan's `argument.type` error:

```php
/** @var Fieldcollection<PriceTier> $prices */
$prices->add(new PriceTier());                // OK
$prices->add(new StockEntry());               // argument.type
$prices->setItems(['tier' => new PriceTier()]); // OK: arbitrary array keys
$prices->setItems([new StockEntry()]);          // argument.type

$product->setVariants(new Fieldcollection());  // OK: a new empty collection stays assignable
```

On a generated getter, the accepted item types come from the generated PHPDoc on 2026.x or the field's allowed types in its class definition on 12.3. Without class definitions, a 12.3 getter accepts any fieldcollection item.

`Fieldcollection` is generic, so at level 6 and up PHPStan reports a bare `Fieldcollection` in your own signatures as `missingType.generics`, on 12.3 the same as on 2026.x. Name the items, e.g. `Fieldcollection<PriceTier>`, or use `Fieldcollection<Fieldcollection\Data\AbstractData>` for any.

### Calculated values

With [class definitions](#configuration), a calculated-value getter using `calculatorType: class` returns `string|null`. Expression calculators retain `mixed`:

```php
$product->getMargin();        // string|null (class calculator)
$product->getComputedPrice(); // mixed (expression calculator)
$product->get('margin');      // string|null
```

A calculator's `compute()` can narrow its object PHPDoc to the classes that use it. When `reportMaybesInMethodSignatures: true` is enabled, the extension suppresses `method.childParameterType` for that parameter only if the type covers every owner:

```php
// Both Product and Category name this calculator in their definitions.
/** @param Product|Category $object */
public function compute(Concrete $object, CalculatedValue $context): string
{
    return $object->getKey() ?? '';
}
```

<details>
<summary>Details</summary>

Calculators in localized fields, objectbricks and fieldcollections receive the owning DataObject class, so the PHPDoc must cover those owners too. Missing or unreadable definitions retain PHPStan's diagnostics. The context parameter, other methods and unrelated errors keep their checks. Calculators referenced as `@service_id` keep their existing getter types and diagnostics.

</details>

### Relation metadata

Advanced relation getters type metadata items with their allowed element classes and configured columns, including
localized fields, objectbricks, fieldcollections and `get('field')` calls:

```php
foreach ($product->getAccessories() as $item) {
    $item->getElement(); // Product|null, when Product is the allowed class
    $item->getNote();    // mixed
    $item->setNote('Spare part'); // void
    $item->getUnknown(); // method.notFound
}
```

<details>
<summary>Details</summary>

Column names are case-insensitive. Column getters return `mixed`; setters accept `mixed` and return `void`. Pimcore's real methods, such as `getData()` and `getElement()`, keep their existing behavior. Columns and allowed element classes need class definitions configured through `pimcore.classDefinitionDirectory`. Without definitions, bare metadata items accept any column. Unknown columns on known items surface as PHPStan's `method.notFound`. Explicit PHPDoc such as `ObjectMetadata<Product, 'note'>` or `ElementMetadata<Category|Asset, 'note'>` also describes known items.

</details>

### Blocks

With class definitions configured, block getters and `get('content')` return arrays of items with typed child keys:

```php
foreach ($product->getContent() ?? [] as $item) {
    if (isset($item['title'])) {
        $item['title']->getData(); // string|null
    }
}
```

Every child key is optional because saved items can predate a newly added child. Unknown keys surface as PHPStan's `offsetAccess.notFound`. Blocks in localizedfields, objectbricks and fieldcollections are supported too. Text, numeric, checkbox, date, select, link, image and relation children get their generated getter types; other children keep `mixed`. A localizedfields child returns plain `Localizedfield|null`, without field-name checking. `setData()` accepts any value, so mutations can invalidate the inferred data type.

Without class definitions, getters retain `BlockElement[][]` and `getData()` returns `mixed`. Constructing `new BlockElement(...)` infers its data type independently of definitions.

## Rules

Every error has an identifier, listed in the rule's section. Silence one with `ignoreErrors` or `@phpstan-ignore`:

```neon
parameters:
    ignoreErrors:
        -
            identifier: pimcore.toggleNotRestored
            path: src/Command/*
```

### Toggle not restored

Pimcore has process-wide switches such as hide-unpublished or inherited values. If your code flips one and doesn't put it back, every later piece of code in the same process, request or worker sees the changed value. The rule reports a toggle that is flipped and not restored in a `finally`:

```php
// Bad: if getById() throws, unpublished objects stay visible
DataObject::setHideUnpublished(false);
$product = Product::getById($id);
DataObject::setHideUnpublished(true);

// Good
$previous = DataObject::getHideUnpublished();
DataObject::setHideUnpublished(false);
try {
    $product = Product::getById($id);
} finally {
    DataObject::setHideUnpublished($previous);
}
```

In each function, method, closure and arrow function, the first call to a toggle's methods flips it and any later call restores it, whatever its argument. The rule doesn't check that you restore the previous value. A restore in the enclosing function doesn't count for a flip in a closure. For inherited values on objects, [`DataObject\Service::useInheritedValues()`](#scoped-callbacks) does all of this for you.

The errors are `pimcore.toggleNotRestored` (never restored) and `pimcore.toggleNotRestoredInFinally` (restored, but not in a `finally`). Code that flips a toggle for the whole process on purpose, such as a console command, is reported too; ignore it as shown [above](#rules).

The built-in toggles, matched by the class that declares the method, so calls through `Product::` or an instance count too:

| Toggle | Class | Methods |
|---|---|---|
| Hide unpublished (objects) | `DataObject\AbstractObject` | `setHideUnpublished` |
| Hide unpublished (documents) | `Document` | `setHideUnpublished` |
| Inherited values (objects) | `DataObject\AbstractObject` | `setGetInheritedValues` |
| Inherited values (documents) | `Document\PageSnippet` | `setGetInheritedValues` |
| Inherited properties | `Element\AbstractElement` | `setGetInheritedProperties` |
| Localized fallback values | `DataObject\Localizedfield` | `setGetFallbackValues` |
| Localized strict mode | `DataObject\Localizedfield` | `setStrictMode` |
| Dirty detection | `DataObject\AbstractObject` | `setDisableDirtyDetection`, `disableDirtyDetection`, `enableDirtyDetection` |
| Versioning | `Version` | `disable`, `enable` |
| Cache | `Pimcore\Cache` | `disable`, `enable` |
| Runtime cache | `Pimcore\Cache\RuntimeCache` | `disable`, `enable` |
| Key and path restoration | `DataObject\AbstractObject` | `setDoNotRestoreKeyAndPath` |
| Inheritance runtime cache | `DataObject\Concrete\Dao\InheritanceHelper` | `setUseRuntimeCache` |
| Immediate cache writes | `Pimcore\Cache` | `setForceImmediateWrite` |
| Mail debug mode | `Pimcore\Mail` | `setForceDebugMode` |
| Dynamic option removal | `DataObject\ClassDefinition\Service` | `setDoRemoveDynamicOptions` |

Classes are under `Pimcore\Model` unless they say otherwise. Add your own toggles with `additionalToggles`. Each entry is one toggle: the class that declares the methods, and the methods. All of them count as flip and restore:

```neon
parameters:
    pimcore:
        additionalToggles:
            - class: App\Feature\Switches
              methods: [disableIndexing, enableIndexing]
```

### Unknown field name

`get('nosuch')` fails at runtime because `getNosuch()` doesn't exist. `getValueForFieldName('nosuch')` doesn't fail; it quietly returns `null`. The rule reports a constant field name that the generated class doesn't have:

```php
// Bad
$product->get('nosuch');                  // there is no getNosuch()
$product->getValueForFieldName('SKU');    // the property is $sku, and the case matters
$product->getValueForFieldName('title');  // a localized field: always null
$product->set('margin', 1);               // a calculated value has no setter
$product->set('sku', 42);                 // setSku() expects ?string
$product->getLocalizedfields()?->getLocalizedValue('Title'); // exact name is 'title'
Product::getByLocalizedfields('Title', 'Example');           // exact name is 'title'

// Good
$product->get('sku');
$product->get('SKU');                     // get() is case-insensitive, like getSku()
$product->getValueForFieldName('sku');
$product->get('title', 'en');
```

<details>
<summary>Details</summary>

`get()` and `getValueFromParent()` accept any name with a matching `get<Name>()` method, whatever the case. `set()` needs a matching `set<Name>()`; calculated values and reverse relations without a setter are reported. With a single constant field name, it also checks the value against the setter's first parameter and reports only values the setter can never accept. Union field names and uncertain value types are skipped for this value check. `getValueForFieldName()` needs the property, with the exact case, or a calculated value (recognised by its getter and its `FIELD_*` constant, also with the exact case). A localized field has no property, so it gets its own message, suggesting `get()` or the getter.

The rule checks generated DataObject classes, brick containers, brick items and fieldcollection items. It doesn't check calls on `Concrete`, `AbstractObject` or anything it doesn't know. Every name in a union of constant strings is checked; a name that isn't a constant is not. On `Localizedfield<Owner>`, `getLocalizedValue()` and `setLocalizedValue()` require an exact localized field name (including localized calculated values); a bare `Localizedfield` is skipped. Static `getByLocalizedfields()` also requires an exact localized field name, without checking filterability. Named arguments are supported for every method.

</details>

The identifiers are `pimcore.unknownFieldName` and `pimcore.fieldValueType` (for impossible `set()` values).

### Unknown definition names

A misspelled definition name makes the lookup return `null`. The rule reports a constant name when its generated class cannot be found, without needing class definitions or a Container XML:

```php
// Bad
ClassDefinition::getByName('Prodcut');
Fieldcollection\Definition::getByKey('PriceTire');
Objectbrick\Definition::getByKey('Electrcial');

// Good
ClassDefinition::getByName('Product');
Fieldcollection\Definition::getByKey('PriceTier');
Objectbrick\Definition::getByKey('Electrical');
```

<details>
<summary>Details</summary>

Generated classes must be available to PHPStan through your project's autoloader or `scanDirectories`. Each name in a union of constant strings is checked. Dynamic names are skipped. Named arguments are supported; custom implementations of the lookup methods are skipped.

</details>

The identifier is `pimcore.unknownDefinitionName`.

### Select options

With class definitions available, constant strings passed to generated setters, `set()` and `Localizedfield::setLocalizedValue()` are checked against static options. The same check covers values arrays passed to `create()`, `setValues()` and `setValue()`. Multiselect values check each element of a constant array. This applies whether or not “enforce validation” is on: Pimcore usually saves invalid values silently when it is off.

```php
// color has options red and green
$product->setColor('blue'); // Value 'blue' is not an option of select field Product::$color.
// Tip: Options: 'red', 'green'.
Product::create(['color' => 'blue']); // Same diagnostic.
$product->setValue('colors', ['red', 'blue']); // Invalid multiselect option 'blue'.
```

<details>
<summary>Details</summary>

Empty values are allowed, and options use Pimcore's loose comparison (`'1'` matches `'01'`). Options providers, field subclasses such as Country, non-constant values and arrays with an unknown shape are skipped. Values arrays must be written directly in the call; unpacked entries are skipped. Classes that redeclare either values method or `create()` are skipped for the affected calls. Without class definitions the rule stays silent.

</details>

The identifier is `pimcore.selectOption`. The options and any did-you-mean suggestion appear in the tip, so changing the options keeps the diagnostic message stable.

### Language arguments

With a Container XML, constant language arguments are checked against `general.valid_languages`. This needs `pimcore.config.config_location.system_settings.read_target.type` to be unset (the default) or `symfony-config`; in both cases Pimcore reads the languages from the container. Matching is exact and case-sensitive: `de-DE` and `de_DE` are different languages.

```php
// general.valid_languages: [en, de]
// Bad
$product->getTitle('fr'); // Language 'fr' is not configured in general.valid_languages.
$product->setTitle('Title', 'fr'); // Same diagnostic.
$product->get('title', 'fr'); // Same diagnostic for a localized field.
$list->setLocale('fr'); // Same diagnostic.

// Good
$product->getTitle('de');
$product->setTitle('Title', 'en');
$product->get('title', 'de');
$list->setLocale('en');
```

<details>
<summary>Details</summary>

The check covers localized getters and setters on generated classes, objectbricks and fieldcollections, `Localizedfield::getLocalizedValue()` / `setLocalizedValue()`, generic `get()` / `set()` for localized fields, localized finders, and `Listing\Concrete::setLocale()`. Named arguments and unions of constant languages are supported. Generated classes identify localized fields; class definitions are optional.

`null`, `''`, dynamic languages and unpacked language arguments are skipped. Without an XML, with an empty or missing language list, or when the system settings read target is anything other than unset or `symfony-config` (such as `settings-store`, `disabled` or an unresolved env placeholder), the rule stays silent. Classificationstore and translation APIs are not checked.

</details>

The identifier is `pimcore.invalidLanguage`.

### Interpolated listing condition

A Listing puts the condition you pass to `setCondition()` or `addConditionParam()` into the SQL as it is. A value concatenated into it is an SQL injection. The rule reports a condition built from a value that isn't known to be safe, and suggests a bound parameter:

```php
// Bad
$list->setCondition("name = '" . $name . "'");
$list->setCondition("name = '$name'");
$list->setCondition(sprintf("name = '%s'", $name));
$list->setOrderKey('o_' . $field, false);

// Good
$list->setCondition('name = ?', [$name]);
$list->addConditionParam('name = ?', $name);
$list->setCondition('id = ' . $id);                        // $id is an int
$list->setCondition(sprintf('id = %d', $id));              // %d makes any value a number
$list->setCondition('name = ' . $list->quote($name));
$list->setCondition('id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')', $ids);
$list->setOrderKey('o_' . $field);                         // Pimcore quotes the identifier
```

The rule checks the first argument of `setCondition()` and `addConditionParam()` on any Listing, and of `setOrderKey()` and `setGroupBy()` when `$quote` may be `false`. It checks only a string built right in the call: a concatenation, an interpolated string, `sprintf()` or `implode()`. A condition in a variable, `setCondition($condition)`, isn't checked, because the rule can't tell where the string came from.

The rule also checks the case-insensitive `condition` key in an array literal passed to DataObject, Asset or Document `getList()` or a Listing's `setValues()`, and the constant key in `setValue('condition', …)`; variable configs, variable conditions, spread items and nonconstant keys are skipped. A Listing config can't bind parameters:

```php
// Bad
Product::getList(['condition' => "sku = '$sku'"]);

// Good
Product::getList()->setCondition('sku = ?', [$sku]);
```

<details>
<summary>Details</summary>

The safe parts are constant strings (a backed enum's `->value` too), `int`, `float`, `bool` and `null` values, and the direct result of `quote()` or `quoteIdentifier()` on the Listing or on the Doctrine connection. Anything else is reported, `escapeLike()` too: it escapes only `%` and `_`. In `sprintf()` a value for a numeric conversion such as `%d` is safe, and the format must be a constant. `implode()` is safe when the array's values are. One error is reported per call, naming the first unsafe part.

</details>

The identifier is `pimcore.interpolatedCondition`.

### Condition placeholders

`IN (?)` needs a nested array: the outer array contains the parameters, and its inner array is the value for that one placeholder.

```php
// Bad: each ID is bound as a separate parameter for one placeholder.
$list->setCondition('id IN (?)', $ids);
$list->addConditionParam('id IN (?)', [1, 2, 3]);
$list->filterById([1, 2, 3], 'IN (?)');
$list->filterBySku('a', 'BETWEEN ? AND ?');

// Good
$list->setCondition('id IN (?)', [$ids]);
$list->addConditionParam('id IN (?)', [[1, 2, 3]]);
$list->setCondition('sku = :sku', ['sku' => $sku]);
$list->filterById([[1, 2, 3]], 'IN (?)');
$list->filterBySku(['a', 'z'], 'BETWEEN ? AND ?');
```

The always enabled rule `pimcore.conditionPlaceholders` checks positional counts, missing or unused named parameters, and conditions mixing `?` and `:name`. Quoted strings, identifiers and comments follow DBAL's MySQL placeholder grammar. A leading colon in a named array key is accepted. A scalar binds one parameter; `setCondition()` with `null` binds none.

Each call is checked on its own, so placeholders filled by another call aren't seen. The rule checks constant SQL strings and concatenations or interpolation with numeric parts. It skips uncertain SQL and parameter counts, optional array keys, and SQL unions with a matching variant. An unknown size array is checked only for the single positional placeholder spread mistake. `setCondition()` without its second argument is skipped, as are values that `addConditionParam()` ignores.

Generated and system `filterBy*()` methods use the same check on constant operators. When the operator contains no `?`, Pimcore appends one, so `filterBySku($sku, 'LIKE')` binds one parameter. Dynamic operators and custom filter methods are skipped.

With class definitions, `pimcore.relationFilterOperator` also catches operators other than `=` on many-to-one relations, which Pimcore rejects:

```php
$list->filterByMainCategory($category, 'IN (?)'); // Bad: only = is supported.
$list->filterByMainCategory($category);           // Good
```

Relation filters that transform their input are excluded from the ordinary placeholder count check. Without class definitions, filters whose generated getters return objects or arrays of objects are skipped. The identifiers are `pimcore.conditionPlaceholders` and `pimcore.relationFilterOperator`.

### Listing arguments

Listing setters and listing config (`getList()` and `setValues()`) report constant arguments that Pimcore rejects, quotes unexpectedly, or ignores:

```php
$list->setOrder('random');              // pimcore.listingOrder
$list->setOrder('DESC');

$list->setOrderKey('RAND()');            // pimcore.listingOrderKey
$list->setOrderKey('RAND()', false);

$list->setConditionVariables($vars);    // pimcore.conditionVariablesIgnored
$list->setCondition($sql, $vars);

$list->setObjectTypes(['variants']);    // pimcore.objectType
$list->setObjectTypes([DataObject::OBJECT_TYPE_VARIANT]);
```

<details>
<summary>Details</summary>

Order checks accept `ASC`, `DESC` (case insensitive) and the empty string. Listings that redeclare `$validOrders` are excluded; runtime `setValidOrders()` calls are not tracked. Quoted order keys and group-by parts are checked for expressions, without validating column names. Pass `false` as the second setter argument to use an expression unquoted. Object types are `object`, `variant` and `folder`; finders support only `object` and `variant`.

Loading a listing rebuilds its condition variables from `setCondition()` and `addConditionParam()`. A custom Dao that reads variables without calling `getCondition()` may need an ignore for `pimcore.conditionVariablesIgnored`.

</details>

### Unknown values keys

Values arrays and constant `setValue()` keys are checked against native setters. Misspelled keys usually disappear silently in Pimcore; the rule reports `pimcore.unknownValuesKey` and suggests a correction when exactly one setter is close.

```php
Product::create(['skuu' => 'ABC']);   // pimcore.unknownValuesKey
Product::create(['sku' => 'ABC']);

Product::getList(['limitt' => 10]);  // pimcore.unknownValuesKey
Product::getList(['limit' => 10]);
```

<details>
<summary>Details</summary>

The rule also reports integer keys, `value` (always ignored), and legacy `o_*` keys (which can throw). Native setters count regardless of visibility or case; PHPDoc `@method` declarations and `__call()` do not. The `id` key is left to Pimcore's exception. No class definitions are needed.

Static `create()` calls cover generated classes, Documents, DataObject folders and Assets. `getList()` keys are checked against the Listing, including a constant `class` selection on DataObjects; a nonconstant `class` skips the call. Instance `setValues()` and `setValue()` calls are checked only on generated classes, generated Listings and final classes in these families. Other instance receivers and classes that redeclare either values method are skipped.

Registered [class overrides](#class-overrides) and Asset [element subtypes](#element-subtypes) that add setters are not visible to this rule. Their extra keys may need an identifier ignore.

</details>

### Element lookup arguments

Element types are case-sensitive: Pimcore expects `'asset'`, `'document'` or `'object'`. The rule checks constant strings in `Element\Service::getElementById()`, `getElementByPath()`, `pathExists()`, `getValidKey()`, `isValidKey()`, `isValidPath()`, `getElementCacheTag()`, `getSessionKey()`, `getElementFromSession()`, `removeElementFromSession()`, `findForbiddenPaths()` and the five `Tag::*ForElement()` methods. Unknown types can silently take the wrong branch; the rule suggests the valid spelling when recognizable.

```php
// Bad
Element\Service::getElementById('dataobject', $id);
Product::getById($id, ['froce' => true]);

// Good
Element\Service::getElementById('object', $id);
Product::getById($id, ['force' => true]);
```

Lookup options accept only `'force' => bool`. The rule checks array literals passed to `DataObject`, `Asset` and `Document` `getById()`/`getByPath()` (including subclasses), and to `getElementById()`. It reports unknown constant keys and values that can never be bool. PHPStan already checks the bool type on DataObject lookups, so the rule avoids duplicate reports there.

Variable arrays, spread items, non-constant keys, dynamic class names and classes that redeclare the lookup method are skipped. Non-constant element types and `pathExists()`'s omitted or null type are allowed.

Element keys containing reserved characters fail validation on save. `setKey()` and literal values arrays in `create()`, `setValues()` and `setValue()` are checked for `/`, plus the reserved characters for that element family. Constant string unions, concatenation parts, interpolated text and literal text in `sprintf()` formats are checked too.

```php
// Bad
$product->setKey('products/example');
Document::create(1, ['key' => 'page?query']);

// Good
$product->setKey('products-example');
Document::create(1, ['key' => 'page-query']);
```

This check skips dynamic values, variable values arrays, spread items and custom key or values methods. It checks the `key` entry, not Asset's `filename` entry. No class definitions are needed.

The identifiers are `pimcore.elementType`, `pimcore.unknownLookupOption`, `pimcore.lookupOptionType` and `pimcore.invalidElementKey`.

### Cache keys

Pimcore's cache keys and tags cannot be empty or contain any of `{}()/\@:`. The rule checks `Pimcore\Cache` and `CoreCacheHandler`, including constant string unions, parts of concatenations and interpolated strings, literal text in `sprintf()` formats, and values in literal or constant tag arrays.

```php
// Bad
Cache::save($data, 'product:' . $id, ['catalog:all']);

// Good
Cache::save($data, 'product_' . $id, ['catalog_all']);
```

`load()` and `clearTag()` throw only when assertions are on; otherwise they silently miss. Dynamic values, spread items and non-constant tag arrays are skipped. `implode()` glue, `RuntimeCache` and Symfony cache pools are not checked.

A load/save pair in the same function is also checked when one variable is assigned from exactly one static `Pimcore\\Cache::load()` call:

```php
$data = Cache::load($locale . self::CACHE_KEY);
if (!$data) {
    $data = $this->buildData();
    Cache::save($data, self::CACHE_KEY); // pimcore.cacheKeyMismatch
}

// Use the same key when saving:
Cache::save($data, $locale . self::CACHE_KEY);
```

<details>
<summary>Details</summary>

`pimcore.cacheKeyMismatch` reports different constant keys, or a concatenation containing the other's whole expression plus extra parts. It checks functions, methods and closures separately, and requires an unconditional load assignment before the save. Multiple loads into the same variable, conditional loads, references, dynamic variable writes and changing key expressions are skipped. Expression matching uses local variables, literals and constants; calls and property reads are skipped. Rebuilding the data in a miss branch is allowed; `CoreCacheHandler`, `RuntimeCache` and code outside a function are not paired.

</details>

The identifiers are `pimcore.cacheReservedCharacters`, `pimcore.cacheEmptyKey` and `pimcore.cacheKeyMismatch`.

### Class override registration

With a [Container XML](#container-xml), `rules.neon` checks the registered class overrides at their use sites:

```php
// Registered: Pimcore\Model\DataObject\Product => App\Model\Product
// Bad
UnregisteredProduct::getById(1); // pimcore.unregisteredClassOverride
new Product();                  // pimcore.bypassedClassOverride
Product::create();               // pimcore.bypassedClassOverride
new Product\Listing();          // pimcore.bypassedClassOverride

// Good: load and construct through the registered class override.
App\Model\Product::getById(1);
new App\Model\Product();
App\Model\Product::create();
App\Model\Product::getList();
```

`pimcore.unregisteredClassOverride` reports static loads (`getById()`, `getByPath()`, finders and `getList()`) on an unregistered subclass, including subclasses below the registered override. Calls on the registered override and its intermediate ancestors are accepted. Redeclared finders and `getList()`, a `getList()` config with a `class` key, and abstract classes outside the intermediate case retain their opt-outs.

`pimcore.bypassedClassOverride` reports direct DataObject or Listing constructions that bypass a registered override. `new static()` and `static::create()` inside the override are accepted. Document and Asset construction is allowed.

`pimcore.invalidClassOverride` reports a registered class that does not exist or does not extend its key, at factory calls, object relation getters and Listing result or iteration sites that would carry it. Those sites keep default typing. Unused broken registrations are silent.

Without a Container XML (or when its override map is absent), these checks are silent and the existing registration assumption remains. The identifiers are `pimcore.unregisteredClassOverride`, `pimcore.bypassedClassOverride` and `pimcore.invalidClassOverride`.

### Event listener wiring

Checks method and class `#[AsEventListener]` attributes and constant-array `EventSubscriberInterface::getSubscribedEvents()` returns. Class attributes support `method:` and Symfony's default method name or `__invoke`; subscribers support a method name, `[method, priority]`, or a list of those pairs.

`pimcore.listenerEventType` reports a listener whose first parameter definitely rejects the event Pimcore dispatches:

```php
// Bad: the event carries a DataObject, not an Asset.
#[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
public function update(AssetEvent $event): void {}

// Good.
#[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
public function update(DataObjectEvent $event): void {}
```

`pimcore.listenerWithoutEventName` reports attributes that infer a Pimcore event class or interface from the parameter without naming the event. Pimcore dispatches named events, so add `event:`:

```php
// Bad: Symfony listens for the event class name.
#[AsEventListener]
public function update(DataObjectEvent $event): void {}

// Good.
#[AsEventListener(event: DataObjectEvents::POST_UPDATE)]
public function update(DataObjectEvent $event): void {}
```

<details>
<summary>Details</summary>

The checks skip unknown or nonconstant event names, missing methods, untyped parameters, and uncertain type comparisons. Compatible unions and supertypes are accepted. `GenericEvent` and Symfony's base `Event` do not trigger the missing-name check. With a Container XML, the check also reads YAML/XML service listeners from `debug.event_dispatcher` and reports incompatible parameters at the listener method. Service aliases are followed; abstract, factory-built and unresolved services, and firewall dispatchers, are skipped. Pairs already checked through attributes or subscribers produce no extra diagnostic. Without a Container XML, attributes and subscribers are checked as before.

The shipped map contains event names from every `*Events` class in `pimcore/pimcore`, including bundled bundles, across both 12.3 and 2026. Separate packages such as e-commerce, admin-ui-classic, and data-hub are outside its scope.

</details>

### Workflow names

With a Container XML, checks literal names in Symfony's workflow API. Names come from every `Symfony\Component\Workflow\Definition` service, including bundle and framework workflows. Workflow names use the names wired into `Workflow` and `StateMachine` services.

```php
// Bad: no workflow, transition or place with these names exists.
$registry->get($product, 'edtiroial'); // pimcore.unknownWorkflow
$workflow->apply($product, 'publsih'); // pimcore.unknownWorkflowTransition
$marking->has('publsihed'); // pimcore.unknownWorkflowPlace

// Good: these names exist in the Container XML.
$registry->has($product, 'product_editorial');
$workflow->can($product, 'publish');
$marking->has('published');
```

<details>
<summary>Details</summary>

`Registry::get()` and `has()` check the workflow name. `WorkflowInterface::apply()` and `can()` check transitions, and `Marking::has()` checks places. Transitions and places are checked against all workflows combined; the rule does not infer which workflow a subject uses. Dynamic names are skipped. Without a Container XML, or if any definition or its name wiring cannot be parsed completely, all three checks stay silent.

</details>

The identifiers are `pimcore.unknownWorkflow`, `pimcore.unknownWorkflowTransition` and `pimcore.unknownWorkflowPlace`.

## Known limitations

A custom `listingParentClass` disables the generic Listing stub chain for the project. Without that chain, Listing iteration keeps Pimcore's existing value and key types.

Class definitions are optional. Without them, `foreach` over a Listing isn't typed, calculated-value getters keep `mixed`, select fields aren't narrowed to their option literals, the select option and relation filter operator rules stay silent, relation metadata lacks configured columns and element types, and blocks lack typed child keys and data. On 12.3, fieldcollection getters also aren't narrowed to their item classes.

Without a Container XML, a subclass of a generated class is assumed to be the registered [class override](#class-overrides).

[Workflow](#workflow-names) transitions and places are checked against all workflows combined, not against the workflow a subject uses.

The extension reads your generated classes and doesn't regenerate them. If they're out of date, so are the types. The Container XML is also stale until you rebuild the debug cache.

Location-aware config, such as thumbnails and document types, may also live in the database. The XML alone cannot generally prove an entry is missing; thumbnail and document type name checks are deferred.

A `ReverseObjectRelation` field passed to `getValueForFieldName()` always returns `null`, but the [unknown field name](#unknown-field-name) rule doesn't report it: like a calculated value, it has a getter and a `FIELD_*` constant but no property, and the rule can't tell the two apart.

Pimcore 11.x and 12.0 to 12.2 aren't supported.

## Development

```bash
composer check
```

This runs ECS, PHPStan and PHPUnit.

### Fixtures

The generated classes in `tests/fixtures/classes/`, class definitions in `tests/fixtures/definitions/`, and synthetic Container XML in `tests/fixtures/container/generated/` are regenerated together on both supported Pimcore lines without a database or a Pimcore install:

```bash
tools/fixture-generator/generate.sh
```

The Container XML comes from a minimal Pimcore kernel that builds and compiles the container without booting it. It includes a Product class override, English and German, a Product workflow, and YAML, attribute and subscriber event wiring. Its secret and database URL are dummy values. Absolute repository paths are normalized to `/fixture` so regeneration is deterministic.

See [tests/fixtures/README.md](tests/fixtures/README.md) for the fixture fields and configuration sources.

### Event map

To regenerate the event map, install both fixture dependency sets under `tools/fixture-generator/vendor-12.3` and `vendor-2026`, then run:

```bash
php tools/event-map/generate.php
```

The generator reads event annotations and applies `tools/event-map/corrections.php` for annotations that are missing or disagree with dispatch sites. The committed map stores only event names and class names.

## License

This package is released under the [MIT License](LICENSE).

Pimcore is not part of this package and has its own license. Using this extension with Pimcore doesn't change the terms under which you use Pimcore; check Pimcore's license for your version.
