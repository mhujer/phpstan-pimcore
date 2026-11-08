<?php

return \Pimcore\Model\DataObject\Objectbrick\Definition::__set_state(array(
   'dao' => NULL,
   'key' => 'Electrical',
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
         'name' => 'voltage',
         'title' => 'Voltage',
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
