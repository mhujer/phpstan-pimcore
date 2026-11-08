<?php

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'dao' => NULL,
   'id' => 'product',
   'name' => 'Product',
   'title' => '',
   'description' => '',
   'creationDate' => NULL,
   'modificationDate' => NULL,
   'userOwner' => NULL,
   'userModification' => NULL,
   'parentClass' => '',
   'implementsInterfaces' => NULL,
   'listingParentClass' => '',
   'useTraits' => '',
   'listingUseTraits' => '',
   'encryption' => false,
   'encryptedTables' => 
  array (
  ),
   'allowInherit' => false,
   'allowVariants' => false,
   'showVariants' => false,
   'layoutDefinitions' => 
  \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'name' => 'pimcore_root',
     'type' => NULL,
     'region' => NULL,
     'title' => NULL,
     'width' => 0,
     'height' => 0,
     'collapsible' => false,
     'collapsed' => false,
     'bodyStyle' => NULL,
     'datatype' => 'layout',
     'children' => 
    array (
      0 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
         'name' => 'sku',
         'title' => 'Sku',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'columnLength' => 190,
         'regex' => '',
         'regexFlags' => 
        array (
        ),
         'unique' => false,
         'showCharCount' => false,
         'width' => NULL,
         'defaultValueGenerator' => '',
      )),
      1 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
         'name' => 'name',
         'title' => 'Name',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'columnLength' => 190,
         'regex' => '',
         'regexFlags' => 
        array (
        ),
         'unique' => false,
         'showCharCount' => false,
         'width' => NULL,
         'defaultValueGenerator' => '',
      )),
      2 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
         'name' => 'price',
         'title' => 'Price',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'integer' => false,
         'unsigned' => false,
         'minValue' => NULL,
         'maxValue' => NULL,
         'unique' => false,
         'decimalSize' => NULL,
         'decimalPrecision' => NULL,
         'width' => NULL,
         'defaultValueGenerator' => '',
      )),
      3 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
         'name' => 'active',
         'title' => 'Active',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'defaultValueGenerator' => '',
      )),
      4 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Date::__set_state(array(
         'name' => 'releaseDate',
         'title' => 'ReleaseDate',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'useCurrentDate' => false,
         'columnType' => 'bigint(20)',
         'defaultValueGenerator' => '',
      )),
      5 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Image::__set_state(array(
         'name' => 'image',
         'title' => 'Image',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'width' => NULL,
         'height' => NULL,
      )),
      6 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
         'name' => 'localizedfields',
         'title' => 'Localizedfields',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'children' => 
        array (
          0 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
             'name' => 'title',
             'title' => 'Title',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'columnLength' => 190,
             'regex' => '',
             'regexFlags' => 
            array (
            ),
             'unique' => false,
             'showCharCount' => false,
             'width' => NULL,
             'defaultValueGenerator' => '',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Textarea::__set_state(array(
             'name' => 'description',
             'title' => 'Description',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'maxLength' => NULL,
             'showCharCount' => false,
             'excludeFromSearchIndex' => false,
             'height' => NULL,
             'width' => NULL,
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
             'name' => 'season',
             'title' => 'Season',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'options' => 
            array (
              0 => 
              array (
                'key' => 'Summer',
                'value' => 'summer',
              ),
              1 => 
              array (
                'key' => 'Winter',
                'value' => 'winter',
              ),
            ),
             'defaultValue' => NULL,
             'columnLength' => 190,
             'dynamicOptions' => false,
             'enforceValidation' => false,
             'defaultValueGenerator' => '',
             'width' => NULL,
             'optionsProviderType' => NULL,
             'optionsProviderClass' => NULL,
             'optionsProviderData' => NULL,
          )),
          3 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Block::__set_state(array(
             'name' => 'localizedContent',
             'title' => 'LocalizedContent',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'lazyLoading' => false,
             'disallowAddRemove' => false,
             'disallowReorder' => false,
             'collapsible' => false,
             'collapsed' => false,
             'maxItems' => NULL,
             'styleElement' => '',
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                 'name' => 'title',
                 'title' => 'Title',
                 'tooltip' => NULL,
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => NULL,
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => true,
                 'visibleSearch' => true,
                 'blockedVarsForExport' => 
                array (
                ),
                 'defaultValue' => NULL,
                 'columnLength' => 190,
                 'regex' => '',
                 'regexFlags' => 
                array (
                ),
                 'unique' => false,
                 'showCharCount' => false,
                 'width' => NULL,
                 'defaultValueGenerator' => '',
              )),
            ),
             'layout' => NULL,
             'referencedFields' => 
            array (
            ),
             'fieldDefinitionsCache' => NULL,
          )),
          4 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
             'name' => 'localizedAccessories',
             'title' => 'LocalizedAccessories',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => true,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'classes' => 
            array (
            ),
             'displayMode' => NULL,
             'pathFormatterClass' => NULL,
             'maxItems' => NULL,
             'visibleFields' => NULL,
             'allowToCreateNewObject' => true,
             'allowToClearRelation' => true,
             'optimizedAdminLoading' => false,
             'enableTextSelection' => false,
             'visibleFieldDefinitions' => 
            array (
            ),
             'width' => NULL,
             'height' => NULL,
             'allowedClassId' => 'Product',
             'columns' => 
            array (
              0 => 
              array (
                'key' => 'note',
                'label' => 'Note',
                'type' => 'input',
                'position' => 1,
              ),
            ),
             'columnKeys' => 
            array (
              0 => 'note',
            ),
             'enableBatchEdit' => false,
             'allowMultipleAssignments' => false,
          )),
        ),
         'region' => NULL,
         'layout' => NULL,
         'maxTabs' => NULL,
         'border' => false,
         'provideSplitView' => false,
         'tabPosition' => 'top',
         'hideLabelsWhenTabsReached' => NULL,
         'referencedFields' => 
        array (
        ),
         'permissionView' => NULL,
         'permissionEdit' => NULL,
         'labelWidth' => 100,
         'labelAlign' => 'left',
         'width' => NULL,
         'height' => NULL,
         'fieldDefinitionsCache' => NULL,
      )),
      7 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
         'name' => 'color',
         'title' => 'Color',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'options' => 
        array (
          0 => 
          array (
            'key' => 'Red',
            'value' => 'red',
          ),
          1 => 
          array (
            'key' => 'Green',
            'value' => 'green',
          ),
        ),
         'defaultValue' => NULL,
         'columnLength' => 190,
         'dynamicOptions' => false,
         'enforceValidation' => false,
         'defaultValueGenerator' => '',
         'width' => NULL,
         'optionsProviderType' => NULL,
         'optionsProviderClass' => NULL,
         'optionsProviderData' => NULL,
      )),
      8 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
         'name' => 'size',
         'title' => 'Size',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'defaultValue' => NULL,
         'columnLength' => 190,
         'dynamicOptions' => false,
         'enforceValidation' => false,
         'defaultValueGenerator' => '',
         'width' => NULL,
         'optionsProviderType' => 'class',
         'optionsProviderClass' => 'App\\OptionsProvider\\SizeOptionsProvider',
         'optionsProviderData' => NULL,
      )),
      9 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect::__set_state(array(
         'name' => 'colors',
         'title' => 'Colors',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'options' => 
        array (
          0 => 
          array (
            'key' => 'Red',
            'value' => 'red',
          ),
          1 => 
          array (
            'key' => 'Green',
            'value' => 'green',
          ),
        ),
         'maxItems' => NULL,
         'renderType' => NULL,
         'enforceValidation' => false,
         'dynamicOptions' => false,
         'defaultValue' => NULL,
         'height' => NULL,
         'width' => NULL,
         'defaultValueGenerator' => '',
         'optionsProviderType' => NULL,
         'optionsProviderClass' => NULL,
         'optionsProviderData' => NULL,
      )),
      10 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect::__set_state(array(
         'name' => 'sizes',
         'title' => 'Sizes',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'maxItems' => NULL,
         'renderType' => NULL,
         'enforceValidation' => false,
         'dynamicOptions' => false,
         'defaultValue' => NULL,
         'height' => NULL,
         'width' => NULL,
         'defaultValueGenerator' => '',
         'optionsProviderType' => 'class',
         'optionsProviderClass' => 'App\\OptionsProvider\\SizeOptionsProvider',
         'optionsProviderData' => NULL,
      )),
      11 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
         'name' => 'mainCategory',
         'title' => 'MainCategory',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => true,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'classes' => 
        array (
          0 => 
          array (
            'classes' => 'Category',
          ),
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'assetInlineDownloadAllowed' => false,
         'allowToClearRelation' => true,
         'objectsAllowed' => true,
         'assetsAllowed' => false,
         'assetTypes' => 
        array (
        ),
         'documentsAllowed' => false,
         'documentTypes' => 
        array (
        ),
         'width' => NULL,
      )),
      12 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
         'name' => 'categories',
         'title' => 'Categories',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => true,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'classes' => 
        array (
          0 => 
          array (
            'classes' => 'Category',
          ),
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'maxItems' => NULL,
         'visibleFields' => NULL,
         'allowToCreateNewObject' => true,
         'allowToClearRelation' => true,
         'optimizedAdminLoading' => false,
         'enableTextSelection' => false,
         'visibleFieldDefinitions' => 
        array (
        ),
         'width' => NULL,
         'height' => NULL,
      )),
      13 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
         'name' => 'related',
         'title' => 'Related',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => true,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'classes' => 
        array (
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'maxItems' => NULL,
         'assetInlineDownloadAllowed' => false,
         'allowToClearRelation' => true,
         'objectsAllowed' => true,
         'assetsAllowed' => true,
         'assetTypes' => 
        array (
        ),
         'documentsAllowed' => true,
         'documentTypes' => 
        array (
        ),
         'enableTextSelection' => false,
         'width' => NULL,
         'height' => NULL,
      )),
      14 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation::__set_state(array(
         'name' => 'accessories',
         'title' => 'Accessories',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => true,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'classes' => 
        array (
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'maxItems' => NULL,
         'visibleFields' => NULL,
         'allowToCreateNewObject' => true,
         'allowToClearRelation' => true,
         'optimizedAdminLoading' => false,
         'enableTextSelection' => false,
         'visibleFieldDefinitions' => 
        array (
        ),
         'width' => NULL,
         'height' => NULL,
         'allowedClassId' => 'Product',
         'columns' => 
        array (
          0 => 
          array (
            'key' => 'note',
            'label' => 'Note',
            'type' => 'input',
            'position' => 1,
          ),
          1 => 
          array (
            'key' => 'qty',
            'label' => 'Quantity',
            'type' => 'number',
            'position' => 2,
          ),
          2 => 
          array (
            'key' => 'data',
            'label' => 'Data',
            'type' => 'input',
            'position' => 3,
          ),
        ),
         'columnKeys' => 
        array (
          0 => 'note',
          1 => 'qty',
          2 => 'data',
        ),
         'enableBatchEdit' => false,
         'allowMultipleAssignments' => false,
      )),
      15 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation::__set_state(array(
         'name' => 'references',
         'title' => 'References',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => true,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'classes' => 
        array (
          0 => 
          array (
            'classes' => 'Category',
          ),
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'maxItems' => NULL,
         'assetInlineDownloadAllowed' => false,
         'allowToClearRelation' => true,
         'objectsAllowed' => true,
         'assetsAllowed' => true,
         'assetTypes' => 
        array (
        ),
         'documentsAllowed' => false,
         'documentTypes' => 
        array (
        ),
         'enableTextSelection' => false,
         'width' => NULL,
         'height' => NULL,
         'columns' => 
        array (
          0 => 
          array (
            'key' => 'note',
            'label' => 'Note',
            'type' => 'input',
            'position' => 1,
          ),
        ),
         'columnKeys' => 
        array (
          0 => 'note',
        ),
         'phpdocType' => '\\Pimcore\\Model\\DataObject\\Data\\ElementMetadata[]',
         'optimizedAdminLoading' => false,
         'enableBatchEdit' => false,
         'allowMultipleAssignments' => false,
      )),
      16 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Block::__set_state(array(
         'name' => 'content',
         'title' => 'Content',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'lazyLoading' => false,
         'disallowAddRemove' => false,
         'disallowReorder' => false,
         'collapsible' => false,
         'collapsed' => false,
         'maxItems' => NULL,
         'styleElement' => '',
         'children' => 
        array (
          0 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
             'name' => 'title',
             'title' => 'Title',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'columnLength' => 190,
             'regex' => '',
             'regexFlags' => 
            array (
            ),
             'unique' => false,
             'showCharCount' => false,
             'width' => NULL,
             'defaultValueGenerator' => '',
          )),
          1 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Textarea::__set_state(array(
             'name' => 'text',
             'title' => 'Text',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'maxLength' => NULL,
             'showCharCount' => false,
             'excludeFromSearchIndex' => false,
             'height' => NULL,
             'width' => NULL,
          )),
          2 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Wysiwyg::__set_state(array(
             'name' => 'html',
             'title' => 'Html',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'toolbarConfig' => '',
             'excludeFromSearchIndex' => false,
             'maxCharacters' => 0,
             'height' => NULL,
             'width' => NULL,
          )),
          3 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
             'name' => 'amount',
             'title' => 'Amount',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'integer' => false,
             'unsigned' => false,
             'minValue' => NULL,
             'maxValue' => NULL,
             'unique' => false,
             'decimalSize' => NULL,
             'decimalPrecision' => NULL,
             'width' => NULL,
             'defaultValueGenerator' => '',
          )),
          4 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Checkbox::__set_state(array(
             'name' => 'enabled',
             'title' => 'Enabled',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'defaultValueGenerator' => '',
          )),
          5 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Date::__set_state(array(
             'name' => 'date',
             'title' => 'Date',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'useCurrentDate' => false,
             'columnType' => 'bigint(20)',
             'defaultValueGenerator' => '',
          )),
          6 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Datetime::__set_state(array(
             'name' => 'datetime',
             'title' => 'Datetime',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'useCurrentDate' => false,
             'respectTimezone' => true,
             'columnType' => 'bigint(20)',
             'defaultValueGenerator' => '',
          )),
          7 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Link::__set_state(array(
             'name' => 'link',
             'title' => 'Link',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'allowedTypes' => NULL,
             'allowedAssetSubtypes' => NULL,
             'allowedDocumentSubtypes' => NULL,
             'allowedObjectSubtypes' => NULL,
             'allowedClasses' => NULL,
             'allowedTargets' => NULL,
             'disabledFields' => NULL,
          )),
          8 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Image::__set_state(array(
             'name' => 'image',
             'title' => 'Image',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'width' => NULL,
             'height' => NULL,
          )),
          9 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Multiselect::__set_state(array(
             'name' => 'colors',
             'title' => 'Colors',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'options' => 
            array (
              0 => 
              array (
                'key' => 'Red',
                'value' => 'red',
              ),
              1 => 
              array (
                'key' => 'Green',
                'value' => 'green',
              ),
            ),
             'maxItems' => NULL,
             'renderType' => NULL,
             'enforceValidation' => false,
             'dynamicOptions' => false,
             'defaultValue' => NULL,
             'height' => NULL,
             'width' => NULL,
             'defaultValueGenerator' => '',
             'optionsProviderType' => NULL,
             'optionsProviderClass' => NULL,
             'optionsProviderData' => NULL,
          )),
          10 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
             'name' => 'categories',
             'title' => 'Categories',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => true,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'classes' => 
            array (
              0 => 
              array (
                'classes' => 'Category',
              ),
            ),
             'displayMode' => NULL,
             'pathFormatterClass' => NULL,
             'maxItems' => NULL,
             'visibleFields' => NULL,
             'allowToCreateNewObject' => true,
             'allowToClearRelation' => true,
             'optimizedAdminLoading' => false,
             'enableTextSelection' => false,
             'visibleFieldDefinitions' => 
            array (
            ),
             'width' => NULL,
             'height' => NULL,
          )),
          11 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
             'name' => 'relations',
             'title' => 'Related',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => true,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'classes' => 
            array (
            ),
             'displayMode' => NULL,
             'pathFormatterClass' => NULL,
             'maxItems' => NULL,
             'assetInlineDownloadAllowed' => false,
             'allowToClearRelation' => true,
             'objectsAllowed' => true,
             'assetsAllowed' => true,
             'assetTypes' => 
            array (
            ),
             'documentsAllowed' => true,
             'documentTypes' => 
            array (
            ),
             'enableTextSelection' => false,
             'width' => NULL,
             'height' => NULL,
          )),
          12 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
             'name' => 'provider',
             'title' => 'Size',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'columnLength' => 190,
             'dynamicOptions' => false,
             'enforceValidation' => false,
             'defaultValueGenerator' => '',
             'width' => NULL,
             'optionsProviderType' => 'class',
             'optionsProviderClass' => 'App\\OptionsProvider\\SizeOptionsProvider',
             'optionsProviderData' => NULL,
          )),
          13 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
             'name' => 'qty',
             'title' => 'Qty',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'defaultValue' => NULL,
             'integer' => true,
             'unsigned' => false,
             'minValue' => NULL,
             'maxValue' => NULL,
             'unique' => false,
             'decimalSize' => NULL,
             'decimalPrecision' => NULL,
             'width' => NULL,
             'defaultValueGenerator' => '',
          )),
          14 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
             'name' => 'color',
             'title' => 'Color',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'options' => 
            array (
              0 => 
              array (
                'key' => 'Red',
                'value' => 'red',
              ),
              1 => 
              array (
                'key' => 'Green',
                'value' => 'green',
              ),
            ),
             'defaultValue' => NULL,
             'columnLength' => 190,
             'dynamicOptions' => false,
             'enforceValidation' => false,
             'defaultValueGenerator' => '',
             'width' => NULL,
             'optionsProviderType' => NULL,
             'optionsProviderClass' => NULL,
             'optionsProviderData' => NULL,
          )),
          15 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation::__set_state(array(
             'name' => 'category',
             'title' => 'Category',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => true,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'classes' => 
            array (
              0 => 
              array (
                'classes' => 'Category',
              ),
            ),
             'displayMode' => NULL,
             'pathFormatterClass' => NULL,
             'assetInlineDownloadAllowed' => false,
             'allowToClearRelation' => true,
             'objectsAllowed' => true,
             'assetsAllowed' => false,
             'assetTypes' => 
            array (
            ),
             'documentsAllowed' => false,
             'documentTypes' => 
            array (
            ),
             'width' => NULL,
          )),
          16 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields::__set_state(array(
             'name' => 'localizedfields',
             'title' => 'Localizedfields',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'children' => 
            array (
              0 => 
              \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
                 'name' => 'caption',
                 'title' => 'Caption',
                 'tooltip' => NULL,
                 'mandatory' => false,
                 'noteditable' => false,
                 'index' => false,
                 'locked' => false,
                 'style' => NULL,
                 'permissions' => NULL,
                 'fieldtype' => '',
                 'relationType' => false,
                 'invisible' => false,
                 'visibleGridView' => true,
                 'visibleSearch' => true,
                 'blockedVarsForExport' => 
                array (
                ),
                 'defaultValue' => NULL,
                 'columnLength' => 190,
                 'regex' => '',
                 'regexFlags' => 
                array (
                ),
                 'unique' => false,
                 'showCharCount' => false,
                 'width' => NULL,
                 'defaultValueGenerator' => '',
              )),
            ),
             'region' => NULL,
             'layout' => NULL,
             'maxTabs' => NULL,
             'border' => false,
             'provideSplitView' => false,
             'tabPosition' => 'top',
             'hideLabelsWhenTabsReached' => NULL,
             'referencedFields' => 
            array (
            ),
             'permissionView' => NULL,
             'permissionEdit' => NULL,
             'labelWidth' => 100,
             'labelAlign' => 'left',
             'width' => NULL,
             'height' => NULL,
             'fieldDefinitionsCache' => NULL,
          )),
          17 => 
          \Pimcore\Model\DataObject\ClassDefinition\Data\Geopoint::__set_state(array(
             'name' => 'location',
             'title' => 'Location',
             'tooltip' => NULL,
             'mandatory' => false,
             'noteditable' => false,
             'index' => false,
             'locked' => false,
             'style' => NULL,
             'permissions' => NULL,
             'fieldtype' => '',
             'relationType' => false,
             'invisible' => false,
             'visibleGridView' => true,
             'visibleSearch' => true,
             'blockedVarsForExport' => 
            array (
            ),
             'lat' => 0.0,
             'lng' => 0.0,
             'zoom' => 1,
             'mapType' => 'roadmap',
             'height' => NULL,
             'width' => NULL,
          )),
        ),
         'layout' => NULL,
         'referencedFields' => 
        array (
        ),
         'fieldDefinitionsCache' => NULL,
      )),
      17 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks::__set_state(array(
         'name' => 'attributes',
         'title' => 'Attributes',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'allowedTypes' => 
        array (
          0 => 'Dimensions',
          1 => 'Electrical',
        ),
         'maxItems' => NULL,
         'border' => false,
      )),
      18 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
         'name' => 'variants',
         'title' => 'Variants',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'allowedTypes' => 
        array (
          0 => 'PriceTier',
          1 => 'StockEntry',
        ),
         'lazyLoading' => false,
         'maxItems' => NULL,
         'disallowAddRemove' => false,
         'disallowReorder' => false,
         'collapsed' => false,
         'collapsible' => false,
         'border' => false,
      )),
      19 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\CalculatedValue::__set_state(array(
         'name' => 'margin',
         'title' => 'Margin',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'elementType' => 'input',
         'calculatorType' => 'class',
         'calculatorExpression' => NULL,
         'calculatorClass' => 'App\\Calculator\\SharedCalculator',
         'calculatorData' => NULL,
         'columnLength' => 190,
         'width' => NULL,
      )),
      20 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\CalculatedValue::__set_state(array(
         'name' => 'computedPrice',
         'title' => 'ComputedPrice',
         'tooltip' => NULL,
         'mandatory' => false,
         'noteditable' => false,
         'index' => false,
         'locked' => false,
         'style' => NULL,
         'permissions' => NULL,
         'fieldtype' => '',
         'relationType' => false,
         'invisible' => false,
         'visibleGridView' => true,
         'visibleSearch' => true,
         'blockedVarsForExport' => 
        array (
        ),
         'elementType' => 'input',
         'calculatorType' => 'expression',
         'calculatorExpression' => 'object.getPrice() * 2',
         'calculatorData' => NULL,
         'columnLength' => 190,
         'width' => NULL,
      )),
    ),
     'locked' => false,
     'blockedVarsForExport' => 
    array (
    ),
     'fieldtype' => 'panel',
     'layout' => NULL,
     'border' => false,
     'icon' => NULL,
     'labelWidth' => 100,
     'labelAlign' => 'left',
  )),
   'icon' => NULL,
   'group' => NULL,
   'showAppLoggerTab' => false,
   'linkGeneratorReference' => NULL,
   'previewGeneratorReference' => NULL,
   'compositeIndices' => 
  array (
  ),
   'showFieldLookup' => false,
   'propertyVisibility' => 
  array (
    'grid' => 
    array (
      'id' => true,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
    'search' => 
    array (
      'id' => true,
      'path' => true,
      'published' => true,
      'modificationDate' => true,
      'creationDate' => true,
    ),
  ),
   'enableGridLocking' => false,
   'deletedDataComponents' => 
  array (
  ),
   'blockedVarsForExport' => 
  array (
  ),
   'fieldDefinitionsCache' => 
  array (
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
