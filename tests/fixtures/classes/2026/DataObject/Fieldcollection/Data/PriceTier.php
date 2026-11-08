<?php

/**
 * Fields Summary:
 * - references [advancedManyToManyRelation]
 * - minQuantity [numeric]
 * - price [numeric]
 * - content [block]
 * -- title [input]
 */

namespace Pimcore\Model\DataObject\Fieldcollection\Data;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

class PriceTier extends DataObject\Fieldcollection\Data\AbstractData
{
public const FIELD_REFERENCES = 'references';
public const FIELD_MIN_QUANTITY = 'minQuantity';
public const FIELD_PRICE = 'price';
public const FIELD_CONTENT = 'content';

protected string $type = "PriceTier";
protected $references;
protected $minQuantity;
protected $price;
protected $content;


/**
* Get references - References
* @return \Pimcore\Model\DataObject\Data\ElementMetadata[]
*/
public function getReferences(): array
{
	$container = $this;
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation $fd */
	$fd = $this->getDefinition()->getFieldDefinition("references");
	$data = $fd->preGetData($container);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set references - References
* @param \Pimcore\Model\DataObject\Data\ElementMetadata[] $references
* @return $this
*/
public function setReferences(?array $references): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation $fd */
	$fd = $this->getDefinition()->getFieldDefinition("references");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getReferences();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $references);
	if (!$isEqual) {
		$this->markFieldDirty("references", true);
	}
	$this->references = $fd->preSetData($this, $references);
	return $this;
}

/**
* Get minQuantity - MinQuantity
* @return float|null
*/
public function getMinQuantity(): ?float
{
	$data = $this->minQuantity;
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set minQuantity - MinQuantity
* @param float|null $minQuantity
* @return $this
*/
public function setMinQuantity(?float $minQuantity): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("minQuantity");
	$this->minQuantity = $fd->preSetData($this, $minQuantity);
	return $this;
}

/**
* Get price - Price
* @return float|null
*/
public function getPrice(): ?float
{
	$data = $this->price;
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set price - Price
* @param float|null $price
* @return $this
*/
public function setPrice(?float $price): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Numeric $fd */
	$fd = $this->getDefinition()->getFieldDefinition("price");
	$this->price = $fd->preSetData($this, $price);
	return $this;
}

/**
* Get content - LocalizedContent
* @return \Pimcore\Model\DataObject\Data\BlockElement[][]
*/
public function getContent(): ?array
{
	$container = $this;
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Block $fd */
	$fd = $this->getDefinition()->getFieldDefinition("content");
	$data = $fd->preGetData($container);
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
public function setContent(?array $content): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Block $fd */
	$fd = $this->getDefinition()->getFieldDefinition("content");
	$this->content = $fd->preSetData($this, $content);
	return $this;
}

}

