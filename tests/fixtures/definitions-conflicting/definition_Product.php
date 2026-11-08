<?php

// Hand-written: a stale definition of Product that contradicts the generated class.

return \Pimcore\Model\DataObject\ClassDefinition::__set_state(array(
   'name' => 'Product',
   'layoutDefinitions' => \Pimcore\Model\DataObject\ClassDefinition\Layout\Panel::__set_state(array(
     'name' => 'pimcore_root',
     'children' => array(
       // The generated getter returns ?float, so string literals don't fit.
       0 => \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
         'name' => 'price',
         'options' => array(0 => array('key' => 'Cheap', 'value' => 'cheap')),
         'optionsProviderType' => NULL,
         'optionsProviderClass' => NULL,
      )),
       // getClassName() is Pimcore's own method, not a getter of the generated class.
       1 => \Pimcore\Model\DataObject\ClassDefinition\Data\Select::__set_state(array(
         'name' => 'className',
         'options' => array(0 => array('key' => 'Product', 'value' => 'Product')),
      )),
       // Not a select here, so there are no options to narrow the generated getter with.
       2 => \Pimcore\Model\DataObject\ClassDefinition\Data\Input::__set_state(array(
         'name' => 'color',
      )),
       // NoSuchItem has no generated item class, so the definition is stale.
       3 => \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
         'name' => 'variants',
         'allowedTypes' => array(0 => 'PriceTier', 1 => 'NoSuchItem'),
      )),
       // The generated getter returns an image, not a fieldcollection.
       4 => \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections::__set_state(array(
         'name' => 'image',
         'allowedTypes' => array(0 => 'PriceTier'),
      )),
       // Stale and incomplete relation definitions keep each feature's own fallback.
       5 => \Pimcore\Model\DataObject\ClassDefinition\Data\Block::__set_state(array(
         'name' => 'content',
         'children' => array(
           \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
             'name' => 'invalid',
             'classes' => array(array('classes' => 'NoSuchItem')),
           )),
           \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
             'name' => 'missing',
           )),
           \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation::__set_state(array(
             'name' => 'noKinds',
           )),
           \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation::__set_state(array(
             'name' => 'emptyName',
             'classes' => array(array('classes' => '')),
           )),
         ),
       )),
       6 => \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation::__set_state(array(
         'name' => 'references',
         'columns' => array(array('key' => 'note')),
         'objectsAllowed' => true,
       )),
    ),
  )),
));
