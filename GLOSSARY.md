# phpstan-pimcore

A PHPStan extension that knows the types and pitfalls of your Pimcore project.

## Language

**Generated class**:
The PHP class Pimcore writes for a DataObject class, objectbrick or fieldcollection, including its per-class Listing. This is the extension's primary source of project knowledge.
_Avoid_: model class, entity

**Class definition**:
The `definition_*.php` file (by default under `var/classes`) that describes a DataObject class, objectbrick or fieldcollection, and from which Pimcore produces the generated class.
_Avoid_: class config, schema

**Class override**:
A class registered in `pimcore.models.class_overrides` that Pimcore builds in place of a Pimcore model class: a generated class, its Listing, or a Document or Asset type. An unregistered subclass is not a class override.
_Avoid_: custom model, extended class

**Finder**:
A static `getByXxx()` method on a generated class. With `limit == 1` it returns a single object or null; with any other limit it returns a Listing.
_Avoid_: magic getter, getBy method

**Static options**:
The options of a select or multiselect field as configured in its class definition, as opposed to options an options provider decides at runtime.
_Avoid_: configured values, enum values

**Metadata column**:
An extra value an advanced relation field stores per related element, read and written through `get<Column>()`/`set<Column>()` on the relation's metadata item; the columns are configured in the class definition.
_Avoid_: relation attribute, extra field

**Block item**:
One entry of a block field: the values of the block's child fields, keyed by child field name.
_Avoid_: block row, block entry

**Listing condition**:
The SQL fragment a Listing adds to its `WHERE` clause (via `setCondition()` or `addConditionParam()`), together with the parameters bound to its placeholders.
_Avoid_: where clause, filter

**Listing config**:
The array of options (condition, order, limit, …) passed to a static `getList()` or to a Listing's `setValues()`, each key applied through the Listing's matching setter.
_Avoid_: list options, getList array

**Values array**:
The array passed to `create()` or `setValues()`, each key applied through the model's matching setter. A Listing config is the values array of a Listing.
_Avoid_: data array, create options

**Element type**:
One of Pimcore's three element kinds, named by the strings `asset`, `document` and `object`. Distinct from a DataObject's object type (`object`, `variant`, `folder`).
_Avoid_: dataobject, object type

**Element subtype**:
The kind of a document or asset that its `getType()` returns (e.g. `page`, `snippet`, `image`). Distinct from an element type and from Pimcore's predefined "Document Types" in settings.
_Avoid_: document type, asset type

**Type map**:
The configured map from each element subtype to the class Pimcore builds for it.
_Avoid_: class map, type definitions

**Toggle**:
A static, process-wide Pimcore switch with a setter and a getter (e.g. hide-unpublished, inherited values, versioning). Code that flips a toggle is expected to restore its previous value.
_Avoid_: flag, global setting

**Scoped callback**:
A Pimcore method that runs a callback with some process-wide state changed (e.g. a toggle flipped), restores the state afterwards, and returns the callback's result.
_Avoid_: wrapper, toggle helper

**Container XML**:
The XML dump of the compiled Symfony container that Pimcore writes in debug mode. The extension's optional source of project config.
_Avoid_: container dump, debug container, config file

**Location-aware config**:
A Pimcore config type (thumbnails, document types, predefined properties, system settings, …) whose entries may live in config files, in the database settings store, or both, depending on its configured read target. The Container XML holds only the file-based entries.
_Avoid_: settings store config, DB config
