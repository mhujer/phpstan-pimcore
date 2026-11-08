<?php

return \Pimcore\Model\DataObject\Objectbrick\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'Dimensions',
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
      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
         'name' => 'width',
         'title' => 'Width',
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
      1 => 
      \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric::__set_state(array(
         'name' => 'height',
         'title' => 'Height',
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
      \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
         'name' => 'unit',
         'title' => 'Unit',
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
            'key' => 'Millimetres',
            'value' => 'mm',
          ),
          1 => 
          array (
            'key' => 'Centimetres',
            'value' => 'cm',
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
         'allowedClassId' => NULL,
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
      4 => 
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
   'classDefinitions' => 
  array (
    0 => 
    array (
      'classname' => 'Product',
      'fieldname' => 'attributes',
    ),
  ),
   'activeDispatchingEvents' => 
  array (
  ),
));
