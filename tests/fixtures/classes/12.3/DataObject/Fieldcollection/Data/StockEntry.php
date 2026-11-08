<?php

/**
 * Fields Summary:
 * - warehouse [input]
 * - quantity [numeric]
 */

namespace Pimcore\Model\DataObject\Fieldcollection\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

class StockEntry extends DataObject\Fieldcollection\Data\AbstractData
{
public const FIELD_WAREHOUSE = 'warehouse';
public const FIELD_QUANTITY = 'quantity';

protected string $type = "StockEntry";
protected $warehouse;
protected $quantity;


/**
* Get warehouse - Warehouse
* @return string|null
*/
public function getWarehouse(): ?string
{
	$data = $this->warehouse;
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set warehouse - Warehouse
* @param string|null $warehouse
* @return $this
*/
public function setWarehouse(?string $warehouse): static
{
	$this->warehouse = $warehouse;

	return $this;
}

/**
* Get quantity - Quantity
* @return float|null
*/
public function getQuantity(): ?float
{
	$data = $this->quantity;
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set quantity - Quantity
* @param float|null $quantity
* @return $this
*/
public function setQuantity(?float $quantity): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("quantity");
	$this->quantity = $fd->preSetData($this, $quantity);
	return $this;
}

}

