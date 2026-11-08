<?php

return \Pimcore\Model\DataObject\Fieldcollection\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'PriceTier',
   'parentClass' => NULL,
   'implementsInterfaces' => NULL,
   'title' => NULL,
   'group' => NULL,
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
        ),
         'displayMode' => NULL,
         'pathFormatterClass' => NULL,
         'maxItems' => NULL,
         'assetInlineDownloadAllowed' => false,
         'allowToClearRelation' => true,
         'objectsAllowed' => true,
         'assetsAllowed' => false,
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
      1 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
         'name' => 'minQuantity',
         'title' => 'MinQuantity',
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
      \Pimcore\Model\DataObject\ClassDefinition\Data\Block::__set_state(array(
         'name' => 'content',
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
   'fieldDefinitionsCache' => NULL,
   'blockedVarsForExport' => 
  array (
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
