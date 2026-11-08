# Test fixtures

`classes/<line>/` holds Pimcore DataObject classes generated from the definitions in
`tools/fixture-generator/definitions.php`, one set per supported Pimcore line:

- `classes/12.3/`: Pimcore 12.3 (platform 2025.4)
- `classes/2026/`: Pimcore 2026.x

The two sets differ in a type-relevant way. Since 2026.x, the fieldcollection getter is typed
`Fieldcollection<...Data\A|...Data\B>|null`; in 12.3 it is plain `Fieldcollection|null`.

`definitions/<line>/` holds the class definitions for the same classes, in the format Pimcore saves
them to `var/classes` (`definition_*.php`, `objectbricks/*.php`, `fieldcollections/*.php`). Tests that
need class definitions point `classDefinitionDirectory` at the set matching the installed line.

`definitions-conflicting/` is hand-written: a stale definition that contradicts the generated classes.

`tests/bootstrap.php` autoloads the set that matches the installed `pimcore/pimcore`.
PHPStan's test container ignores `scanDirectories`, so the classes have to be autoloadable.

The files under `classes/` and `definitions/` are generated output. Do not edit them by hand.

## v0.2 fixtures

Product includes these fields on both lines:

- `colors`: static-options Multiselect (`red`, `green`); `sizes`: provider Multiselect with stale configured options.
- `accessories`: advanced object relation to Product, with metadata columns `note`, `qty` and `data`
  (the last exercises a clash with a real metadata method).
- `references`: advanced relation allowing Category objects and assets, with a `note` metadata column.
- `content`: block with input `title`, integer numeric `qty`, static select `color`, many-to-one
  `category`, `localizedfields` with a `caption`, and unmapped Geopoint `location`.
- `localizedContent`: block inside Product's localized fields, with an input `title` child.
- `localizedAccessories`: advanced object relation to Product inside localized fields, with a `note` metadata column.

Dimensions has an `accessories` advanced object relation with unrestricted object classes. PriceTier has a
`references` advanced relation allowing unrestricted objects and documents. Both have a `note` metadata column.

`overrides/` contains the hand-written `App\Model\Product` class override and its sub-subclass
`App\Model\SpecialProduct`. These are shared by both lines, autoloaded by `tests/bootstrap.php`,
and survive regeneration. They represent a project class override; no configuration or kernel is loaded.

## v0.3 fixtures

Product's `margin` and Category's `label` use the same class calculator,
`App\Calculator\SharedCalculator`; Product's `computedPrice` uses the expression
`object.getPrice() * 2`. The calculator name is fixture metadata, so generation does not execute it.
Product's `variants` fieldcollection allows `PriceTier` and `StockEntry`.

`container/generated/<line>.xml` contains the compiled debug Container XML from each pinned
Pimcore line. `tools/fixture-generator/project/` is a synthetic project with:

- Product's registered class override `App\Model\Product`;
- `general.valid_languages: [en, de]`;
- `product_editorial`, a Product workflow with `draft`, `review` and `published` places and
  `submit` and `publish` transitions;
- a YAML listener for `pimcore.dataobject.preUpdate`, an attribute listener for
  `pimcore.dataobject.postUpdate`, and a subscriber for `pimcore.dataobject.postAdd`;
- Pimcore's default document and asset type maps, a dummy secret and a dummy DBAL URL.

`generate-container.php` builds and compiles a minimal `Pimcore\Kernel` without booting it or
connecting to a DB. The `needs-install.lock` marker bypasses product registration. Cache files
and Symfony's generated `config/reference.php` are ignored. A named kernel and repository paths
normalized to `/fixture` keep the output reproducible. These XML files are generated output;
do not edit them by hand. Feature tests use small hand-written XML snippets, while shape tests
read the generated XML matching the installed line. Real project dumps are never test fixtures.

## Regenerating

No database or Pimcore install is needed:

```bash
tools/fixture-generator/generate.sh
```

The script installs each line's pinned Pimcore (`tools/fixture-generator/composer-<line>.json`)
into `tools/fixture-generator/vendor-<line>/`. It then calls Pimcore's class builders directly
on in-memory `ClassDefinition`s, with a stub kernel and no DB.

To change the fields, edit `definitions.php`; to change the Container XML, edit the synthetic
project config or listener classes. Regenerate and commit both lines of classes, definitions and XML.

To add a Pimcore line, add `composer-<line>.json`, extend the loop in `generate.sh`, and teach
`tests/bootstrap.php` to pick the new set.
