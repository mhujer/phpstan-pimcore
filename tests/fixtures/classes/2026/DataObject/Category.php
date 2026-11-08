<?php

/**
 * Inheritance: no
 * Variants: no
 *
 * Fields Summary:
 * - name [input]
 * - code [input]
 * - label [calculatedValue]
 */

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\Category\Listing getList(array $config = [])
* @method static \Pimcore\Model\DataObject\Category\Listing|\Pimcore\Model\DataObject\Category|null getByKey(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Category\Listing|\Pimcore\Model\DataObject\Category|null getByName(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Category\Listing|\Pimcore\Model\DataObject\Category|null getByCode(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Category\Listing|\Pimcore\Model\DataObject\Category|null getByLabel(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
*/

class Category extends Concrete
{
public const FIELD_NAME = 'name';
public const FIELD_CODE = 'code';
public const FIELD_LABEL = 'label';

protected $classId = "category";
protected $className = "Category";
protected $name;
protected $code;


/**
* @param array $values
* @return static
*/
public static function create(array $values = []): static
{
	$object = new static();
	$object->setValues($values);
	return $object;
}

/**
* Get name - Name
* @return string|null
*/
public function getName(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("name");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->name;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set name - Name
* @param string|null $name
* @return $this
*/
public function setName(?string $name): static
{
	$this->markFieldDirty("name", true);

	$this->name = $name;

	return $this;
}

/**
* Get code - Code
* @return string|null
*/
public function getCode(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("code");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->code;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set code - Code
* @param string|null $code
* @return $this
*/
public function setCode(?string $code): static
{
	$this->markFieldDirty("code", true);

	$this->code = $code;

	return $this;
}

/**
* Get label - Margin
* @return mixed
*/
public function getLabel(): mixed
{
	$data = new \Pimcore\Model\DataObject\Data\CalculatedValue('label');
	$data->setContextualData("object", null, null, null);
	$object = $this;
	$data = \Pimcore\Model\DataObject\Service::getCalculatedFieldValue($object, $data);

	return $data;
}

}

