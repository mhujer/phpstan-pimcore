<?php

namespace Pimcore\Model\DataObject\Product;

use Pimcore\Model;
use Pimcore\Model\DataObject;

/**
 * @method DataObject\Product|false current()
 * @method DataObject\Product[] load()
 * @method DataObject\Product[] getData()
 * @method DataObject\Product[] getObjects()
 */

class Listing extends DataObject\Listing\Concrete
{
protected $classId = "product";
protected $className = "Product";


/**
* Filter by sku (Sku)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterBySku ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("sku")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by name (Name)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByName ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("name")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by price (Price)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByPrice ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("price")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by active (Active)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByActive ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("active")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by releaseDate (ReleaseDate)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByReleaseDate ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("releaseDate")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by image (Image)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByImage ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("image")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by title (Title)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByTitle ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("title")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by description (Description)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByDescription ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("description")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by season (Season)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterBySeason ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("season")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by localizedContent (LocalizedContent)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByLocalizedContent ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("localizedContent")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by localizedAccessories (LocalizedAccessories)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByLocalizedAccessories ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("localizedAccessories")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by color (Color)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByColor ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("color")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by size (Size)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterBySize ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("size")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by colors (Colors)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByColors ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("colors")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by sizes (Sizes)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterBySizes ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("sizes")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by mainCategory (MainCategory)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByMainCategory ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("mainCategory")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by categories (Categories)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByCategories ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("categories")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by related (Related)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByRelated ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("related")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by accessories (Accessories)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByAccessories ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("accessories")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by references (References)
* @param mixed $data
* @param string $operator SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByReferences ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("references")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by margin (Margin)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByMargin ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("margin")->addListingFilter($this, $data, $operator);
	return $this;
}

/**
* Filter by computedPrice (ComputedPrice)
* @param string|int|float|array|Model\Element\ElementInterface $data  comparison data, can be scalar or array (if operator is e.g. "IN (?)")
* @param string $operator  SQL comparison operator, e.g. =, <, >= etc. You can use "?" as placeholder, e.g. "IN (?)"
* @return $this
*/
public function filterByComputedPrice ($data, $operator = '='): static
{
	$this->getClass()->getFieldDefinition("computedPrice")->addListingFilter($this, $data, $operator);
	return $this;
}



}
