<?php

/**
 * Fields Summary:
 * - width [numeric]
 * - height [numeric]
 * - unit [select]
 * - accessories [advancedManyToManyObjectRelation]
 * - content [block]
 * -- title [input]
 */

namespace Pimcore\Model\DataObject\Objectbrick\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;


class Dimensions extends DataObject\Objectbrick\Data\AbstractData
{
public const FIELD_WIDTH = 'width';
public const FIELD_HEIGHT = 'height';
public const FIELD_UNIT = 'unit';
public const FIELD_ACCESSORIES = 'accessories';
public const FIELD_CONTENT = 'content';

protected string $type = "Dimensions";
protected $width;
protected $height;
protected $unit;
protected $accessories;
protected $content;


/**
* Dimensions constructor.
* @param DataObject\Concrete $object
*/
public function __construct(DataObject\Concrete $object)
{
	parent::__construct($object);
	$this->markFieldDirty("_self");
}


/**
* Get width - Width
* @return float|null
*/
public function getWidth(): ?float
{
	$data = $this->width;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("width")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("width");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set width - Width
* @param float|null $width
* @return $this
*/
public function setWidth (?float $width): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("width");
	$this->width = $fd->preSetData($this, $width);
	return $this;
}

/**
* Get height - Height
* @return float|null
*/
public function getHeight(): ?float
{
	$data = $this->height;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("height")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("height");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set height - Height
* @param float|null $height
* @return $this
*/
public function setHeight (?float $height): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("height");
	$this->height = $fd->preSetData($this, $height);
	return $this;
}

/**
* Get unit - Unit
* @return string|null
*/
public function getUnit(): ?string
{
	$data = $this->unit;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("unit")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("unit");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set unit - Unit
* @param string|null $unit
* @return $this
*/
public function setUnit (?string $unit): static
{
	$this->unit = $unit;

	return $this;
}

/**
* Get accessories - Accessories
* @return \Pimcore\Model\DataObject\Data\ObjectMetadata[]
*/
public function getAccessories(): array
{
	$data = $this->getDefinition()->getFieldDefinition("accessories")->preGetData($this);
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("accessories")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("accessories");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set accessories - Accessories
* @param \Pimcore\Model\DataObject\Data\ObjectMetadata[] $accessories
* @return $this
*/
public function setAccessories (?array $accessories): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation $fd */
	$fd = $this->getDefinition()->getFieldDefinition("accessories");
	$class = $this->getObject() ? $this->getObject()->getClass() : null;
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	if ($class && $class->getAllowInherit()) {
		$currentData = \Pimcore\Model\DataObject\Service::useInheritedValues(false, function() {
			return $this->getAccessories();
		});
	}
	else {
		$currentData = $this->getAccessories();
	}	
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $accessories);
	if (!$isEqual) {
		$this->markFieldDirty("accessories", true);
	}
	$this->accessories = $fd->preSetData($this, $accessories);
	return $this;
}

/**
* Get content - LocalizedContent
* @return \Pimcore\Model\DataObject\Data\BlockElement[][]
*/
public function getContent(): ?array
{
	$data = $this->getDefinition()->getFieldDefinition("content")->preGetData($this);
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("content")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("content");
		} catch (InheritanceParentNotFoundException $e) {
			// no data from parent available, continue ...
		}
	}
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set content - LocalizedContent
* @param \Pimcore\Model\DataObject\Data\BlockElement[][] $content
* @return $this
*/
public function setContent (?array $content): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Block $fd */
	$fd = $this->getDefinition()->getFieldDefinition("content");
	$this->content = $fd->preSetData($this, $content);
	return $this;
}

}

