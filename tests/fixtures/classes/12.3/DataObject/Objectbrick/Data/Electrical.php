<?php

/**
 * Fields Summary:
 * - voltage [numeric]
 */

namespace Pimcore\Model\DataObject\Objectbrick\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;


class Electrical extends DataObject\Objectbrick\Data\AbstractData
{
public const FIELD_VOLTAGE = 'voltage';

protected string $type = "Electrical";
protected $voltage;


/**
* Electrical constructor.
* @param DataObject\Concrete $object
*/
public function __construct(DataObject\Concrete $object)
{
	parent::__construct($object);
	$this->markFieldDirty("_self");
}


/**
* Get voltage - Voltage
* @return float|null
*/
public function getVoltage(): ?float
{
	$data = $this->voltage;
	if(\Pimcore\Model\DataObject::doGetInheritedValues($this->getObject()) && $this->getDefinition()->getFieldDefinition("voltage")->isEmpty($data)) {
		try {
			return $this->getValueFromParent("voltage");
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
* Set voltage - Voltage
* @param float|null $voltage
* @return $this
*/
public function setVoltage (?float $voltage): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("voltage");
	$this->voltage = $fd->preSetData($this, $voltage);
	return $this;
}

}

