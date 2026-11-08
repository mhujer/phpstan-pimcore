<?php

/**
 * Inheritance: no
 * Variants: no
 *
 * Fields Summary:
 * - sku [input]
 * - name [input]
 * - price [numeric]
 * - active [checkbox]
 * - releaseDate [date]
 * - image [image]
 * - localizedfields [localizedfields]
 * -- title [input]
 * -- description [textarea]
 * -- season [select]
 * -- localizedContent [block]
 * --- title [input]
 * -- localizedAccessories [advancedManyToManyObjectRelation]
 * - color [select]
 * - size [select]
 * - colors [multiselect]
 * - sizes [multiselect]
 * - mainCategory [manyToOneRelation]
 * - categories [manyToManyObjectRelation]
 * - related [manyToManyRelation]
 * - accessories [advancedManyToManyObjectRelation]
 * - references [advancedManyToManyRelation]
 * - content [block]
 * -- title [input]
 * -- text [textarea]
 * -- html [wysiwyg]
 * -- amount [numeric]
 * -- enabled [checkbox]
 * -- date [date]
 * -- datetime [datetime]
 * -- link [link]
 * -- image [image]
 * -- colors [multiselect]
 * -- categories [manyToManyObjectRelation]
 * -- relations [manyToManyRelation]
 * -- provider [select]
 * -- qty [numeric]
 * -- color [select]
 * -- category [manyToOneRelation]
 * -- localizedfields [localizedfields]
 * --- caption [input]
 * -- location [geopoint]
 * - attributes [objectbricks]
 * - variants [fieldcollections]
 * - margin [calculatedValue]
 * - computedPrice [calculatedValue]
 */

namespace Pimcore\Model\DataObject;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\PreGetValueHookInterface;

/**
* @method static \Pimcore\Model\DataObject\Product\Listing getList(array $config = [])
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByKey(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getBySku(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByName(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByPrice(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByActive(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByReleaseDate(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByImage(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByLocalizedfields(string $field, mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByTitle(mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByDescription(mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getBySeason(mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByLocalizedContent(mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByLocalizedAccessories(mixed $value, ?string $locale = null, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByColor(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getBySize(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByColors(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getBySizes(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByMainCategory(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByCategories(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByRelated(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByAccessories(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByReferences(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByMargin(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
* @method static \Pimcore\Model\DataObject\Product\Listing|\Pimcore\Model\DataObject\Product|null getByComputedPrice(mixed $value, ?int $limit = null, int $offset = 0, ?array $objectTypes = null)
*/

class Product extends Concrete
{
public const FIELD_SKU = 'sku';
public const FIELD_NAME = 'name';
public const FIELD_PRICE = 'price';
public const FIELD_ACTIVE = 'active';
public const FIELD_RELEASE_DATE = 'releaseDate';
public const FIELD_IMAGE = 'image';
public const FIELD_TITLE = 'title';
public const FIELD_DESCRIPTION = 'description';
public const FIELD_SEASON = 'season';
public const FIELD_LOCALIZED_CONTENT = 'localizedContent';
public const FIELD_LOCALIZED_ACCESSORIES = 'localizedAccessories';
public const FIELD_COLOR = 'color';
public const FIELD_SIZE = 'size';
public const FIELD_COLORS = 'colors';
public const FIELD_SIZES = 'sizes';
public const FIELD_MAIN_CATEGORY = 'mainCategory';
public const FIELD_CATEGORIES = 'categories';
public const FIELD_RELATED = 'related';
public const FIELD_ACCESSORIES = 'accessories';
public const FIELD_REFERENCES = 'references';
public const FIELD_CONTENT = 'content';
public const FIELD_ATTRIBUTES = 'attributes';
public const FIELD_VARIANTS = 'variants';
public const FIELD_MARGIN = 'margin';
public const FIELD_COMPUTED_PRICE = 'computedPrice';

protected $classId = "product";
protected $className = "Product";
protected $sku;
protected $name;
protected $price;
protected $active;
protected $releaseDate;
protected $image;
protected $localizedfields;
protected $color;
protected $size;
protected $colors;
protected $sizes;
protected $mainCategory;
protected $categories;
protected $related;
protected $accessories;
protected $references;
protected $content;
protected $attributes;
protected $variants;


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
* Get sku - Sku
* @return string|null
*/
public function getSku(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("sku");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->sku;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set sku - Sku
* @param string|null $sku
* @return $this
*/
public function setSku(?string $sku): static
{
	$this->markFieldDirty("sku", true);

	$this->sku = $sku;

	return $this;
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
* Get price - Price
* @return float|null
*/
public function getPrice(): ?float
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("price");
		if ($preValue !== null) {
			return $preValue;
		}
	}

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
	$fd = $this->getClass()->getFieldDefinition("price");
	$this->price = $fd->preSetData($this, $price);
	return $this;
}

/**
* Get active - Active
* @return bool|null
*/
public function getActive(): ?bool
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("active");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->active;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set active - Active
* @param bool|null $active
* @return $this
*/
public function setActive(?bool $active): static
{
	$this->markFieldDirty("active", true);

	$this->active = $active;

	return $this;
}

/**
* Get releaseDate - ReleaseDate
* @return \Carbon\Carbon|null
*/
public function getReleaseDate(): ?\Carbon\Carbon
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("releaseDate");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->releaseDate;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set releaseDate - ReleaseDate
* @param \Carbon\Carbon|null $releaseDate
* @return $this
*/
public function setReleaseDate(?\Carbon\Carbon $releaseDate): static
{
	$this->markFieldDirty("releaseDate", true);

	$this->releaseDate = $releaseDate;

	return $this;
}

/**
* Get image - Image
* @return \Pimcore\Model\Asset\Image|null
*/
public function getImage(): ?\Pimcore\Model\Asset\Image
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("image");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->image;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set image - Image
* @param \Pimcore\Model\Asset\Image|null $image
* @return $this
*/
public function setImage(?\Pimcore\Model\Asset\Image $image): static
{
	$this->markFieldDirty("image", true);

	$this->image = $image;

	return $this;
}

/**
* Get localizedfields - Localizedfields
* @return \Pimcore\Model\DataObject\Localizedfield|null
*/
public function getLocalizedfields(): ?\Pimcore\Model\DataObject\Localizedfield
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("localizedfields");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("localizedfields")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Get title - Title
* @return string|null
*/
public function getTitle(?string $language = null): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("title");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getLocalizedfields()->getLocalizedValue("title", $language);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Get description - Description
* @return string|null
*/
public function getDescription(?string $language = null): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("description");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getLocalizedfields()->getLocalizedValue("description", $language);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Get season - Season
* @return string|null
*/
public function getSeason(?string $language = null): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("season");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getLocalizedfields()->getLocalizedValue("season", $language);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Get localizedContent - LocalizedContent
* @return \Pimcore\Model\DataObject\Data\BlockElement[][]
*/
public function getLocalizedContent(?string $language = null): ?array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("localizedContent");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getLocalizedfields()->getLocalizedValue("localizedContent", $language);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Get localizedAccessories - LocalizedAccessories
* @return \Pimcore\Model\DataObject\Data\ObjectMetadata[]
*/
public function getLocalizedAccessories(?string $language = null): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("localizedAccessories");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getLocalizedfields()->getLocalizedValue("localizedAccessories", $language);
	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set localizedfields - Localizedfields
* @param \Pimcore\Model\DataObject\Localizedfield|null $localizedfields
* @return $this
*/
public function setLocalizedfields(?\Pimcore\Model\DataObject\Localizedfield $localizedfields): static
{
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getLocalizedfields();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$this->markFieldDirty("localizedfields", true);
	$this->markFieldDirty("localizedfields", true);

	$this->localizedfields = $localizedfields;

	return $this;
}

/**
* Set title - Title
* @param string|null $title
* @return $this
*/
public function setTitle (?string $title, ?string $language = null): static
{
	$isEqual = false;
	$this->getLocalizedfields()->setLocalizedValue("title", $title, $language, !$isEqual);

	return $this;
}

/**
* Set description - Description
* @param string|null $description
* @return $this
*/
public function setDescription (?string $description, ?string $language = null): static
{
	$isEqual = false;
	$this->getLocalizedfields()->setLocalizedValue("description", $description, $language, !$isEqual);

	return $this;
}

/**
* Set season - Season
* @param string|null $season
* @return $this
*/
public function setSeason (?string $season, ?string $language = null): static
{
	$isEqual = false;
	$this->getLocalizedfields()->setLocalizedValue("season", $season, $language, !$isEqual);

	return $this;
}

/**
* Set localizedContent - LocalizedContent
* @param \Pimcore\Model\DataObject\Data\BlockElement[][] $localizedContent
* @return $this
*/
public function setLocalizedContent (?array $localizedContent, ?string $language = null): static
{
	$isEqual = false;
	$this->getLocalizedfields()->setLocalizedValue("localizedContent", $localizedContent, $language, !$isEqual);

	return $this;
}

/**
* Set localizedAccessories - LocalizedAccessories
* @param \Pimcore\Model\DataObject\Data\ObjectMetadata[] $localizedAccessories
* @return $this
*/
public function setLocalizedAccessories (?array $localizedAccessories, ?string $language = null): static
{
	$fd = $this->getClass()->getFieldDefinition("localizedfields")->getFieldDefinition("localizedAccessories");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getLocalizedAccessories($language);
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $localizedAccessories);
	if (!$isEqual) {
		$this->markFieldDirty("localizedAccessories", true);
	}
	$this->getLocalizedfields()->setLocalizedValue("localizedAccessories", $localizedAccessories, $language, !$isEqual);

	return $this;
}

/**
* Get color - Color
* @return string|null
*/
public function getColor(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("color");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->color;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set color - Color
* @param string|null $color
* @return $this
*/
public function setColor(?string $color): static
{
	$this->markFieldDirty("color", true);

	$this->color = $color;

	return $this;
}

/**
* Get size - Size
* @return string|null
*/
public function getSize(): ?string
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("size");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->size;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set size - Size
* @param string|null $size
* @return $this
*/
public function setSize(?string $size): static
{
	$this->markFieldDirty("size", true);

	$this->size = $size;

	return $this;
}

/**
* Get colors - Colors
* @return string[]|null
*/
public function getColors(): ?array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("colors");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->colors;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set colors - Colors
* @param string[]|null $colors
* @return $this
*/
public function setColors(?array $colors): static
{
	$this->markFieldDirty("colors", true);

	$this->colors = $colors;

	return $this;
}

/**
* Get sizes - Sizes
* @return string[]|null
*/
public function getSizes(): ?array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("sizes");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->sizes;

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set sizes - Sizes
* @param string[]|null $sizes
* @return $this
*/
public function setSizes(?array $sizes): static
{
	$this->markFieldDirty("sizes", true);

	$this->sizes = $sizes;

	return $this;
}

/**
* Get mainCategory - MainCategory
* @return \Pimcore\Model\DataObject\Category|null
*/
public function getMainCategory(): ?\Pimcore\Model\Element\AbstractElement
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("mainCategory");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("mainCategory")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set mainCategory - MainCategory
* @param \Pimcore\Model\DataObject\Category|null $mainCategory
* @return $this
*/
public function setMainCategory(?\Pimcore\Model\Element\AbstractElement $mainCategory): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("mainCategory");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getMainCategory();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $mainCategory);
	if (!$isEqual) {
		$this->markFieldDirty("mainCategory", true);
	}
	$this->mainCategory = $fd->preSetData($this, $mainCategory);
	return $this;
}

/**
* Get categories - Categories
* @return \Pimcore\Model\DataObject\Category[]
*/
public function getCategories(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("categories");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("categories")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set categories - Categories
* @param \Pimcore\Model\DataObject\Category[] $categories
* @return $this
*/
public function setCategories(?array $categories): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyObjectRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("categories");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getCategories();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $categories);
	if (!$isEqual) {
		$this->markFieldDirty("categories", true);
	}
	$this->categories = $fd->preSetData($this, $categories);
	return $this;
}

/**
* Get related - Related
* @return \Pimcore\Model\Document\Page[]|\Pimcore\Model\Document\Snippet[]|\Pimcore\Model\Document[]|\Pimcore\Model\Asset[]|\Pimcore\Model\DataObject\AbstractObject[]
*/
public function getRelated(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("related");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("related")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set related - Related
* @param \Pimcore\Model\Document\Page[]|\Pimcore\Model\Document\Snippet[]|\Pimcore\Model\Document[]|\Pimcore\Model\Asset[]|\Pimcore\Model\DataObject\AbstractObject[] $related
* @return $this
*/
public function setRelated(?array $related): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("related");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getRelated();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $related);
	if (!$isEqual) {
		$this->markFieldDirty("related", true);
	}
	$this->related = $fd->preSetData($this, $related);
	return $this;
}

/**
* Get accessories - Accessories
* @return \Pimcore\Model\DataObject\Data\ObjectMetadata[]
*/
public function getAccessories(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("accessories");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("accessories")->preGetData($this);

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
public function setAccessories(?array $accessories): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation $fd */
	$fd = $this->getClass()->getFieldDefinition("accessories");
	$hideUnpublished = \Pimcore\Model\DataObject\Concrete::getHideUnpublished();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished(false);
	$currentData = $this->getAccessories();
	\Pimcore\Model\DataObject\Concrete::setHideUnpublished($hideUnpublished);
	$isEqual = $fd->isEqual($currentData, $accessories);
	if (!$isEqual) {
		$this->markFieldDirty("accessories", true);
	}
	$this->accessories = $fd->preSetData($this, $accessories);
	return $this;
}

/**
* Get references - References
* @return \Pimcore\Model\DataObject\Data\ElementMetadata[]
*/
public function getReferences(): array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("references");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("references")->preGetData($this);

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
	$fd = $this->getClass()->getFieldDefinition("references");
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
* Get content - Content
* @return \Pimcore\Model\DataObject\Data\BlockElement[][]
*/
public function getContent(): ?array
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("content");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("content")->preGetData($this);

	if ($data instanceof \Pimcore\Model\DataObject\Data\EncryptedField) {
		return $data->getPlain();
	}

	return $data;
}

/**
* Set content - Content
* @param \Pimcore\Model\DataObject\Data\BlockElement[][] $content
* @return $this
*/
public function setContent(?array $content): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Block $fd */
	$fd = $this->getClass()->getFieldDefinition("content");
	$this->content = $fd->preSetData($this, $content);
	return $this;
}

/**
* @return \Pimcore\Model\DataObject\Product\Attributes
*/
public function getAttributes(): ?\Pimcore\Model\DataObject\Objectbrick
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("attributes");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->attributes;
	if (!$data) {
		if (\Pimcore\Tool::classExists("\\Pimcore\\Model\\DataObject\\Product\\Attributes")) {
			$data = new \Pimcore\Model\DataObject\Product\Attributes($this, "attributes");
			$this->attributes = $data;
		} else {
			return null;
		}
	}
	return $data;
}

/**
* Set attributes - Attributes
* @param \Pimcore\Model\DataObject\Objectbrick|null $attributes
* @return $this
*/
public function setAttributes(?\Pimcore\Model\DataObject\Objectbrick $attributes): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Objectbricks $fd */
	$fd = $this->getClass()->getFieldDefinition("attributes");
	$this->attributes = $fd->preSetData($this, $attributes);
	return $this;
}

/**
* @return \Pimcore\Model\DataObject\Fieldcollection<\Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|\Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null
*/
public function getVariants(): ?\Pimcore\Model\DataObject\Fieldcollection
{
	if ($this instanceof PreGetValueHookInterface && !\Pimcore::inAdmin()) {
		$preValue = $this->preGetValue("variants");
		if ($preValue !== null) {
			return $preValue;
		}
	}

	$data = $this->getClass()->getFieldDefinition("variants")->preGetData($this);
	return $data;
}

/**
* Set variants - Variants
* @param \Pimcore\Model\DataObject\Fieldcollection<\Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|\Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>|null $variants
* @return $this
*/
public function setVariants(?\Pimcore\Model\DataObject\Fieldcollection $variants): static
{
	/** @var \Pimcore\Model\DataObject\ClassDefinition\Data\Fieldcollections $fd */
	$fd = $this->getClass()->getFieldDefinition("variants");
	$this->variants = $fd->preSetData($this, $variants);
	return $this;
}

/**
* Get margin - Margin
* @return mixed
*/
public function getMargin(): mixed
{
	$data = new \Pimcore\Model\DataObject\Data\CalculatedValue('margin');
	$data->setContextualData("object", null, null, null);
	$object = $this;
	$data = \Pimcore\Model\DataObject\Service::getCalculatedFieldValue($object, $data);

	return $data;
}

/**
* Get computedPrice - ComputedPrice
* @return mixed
*/
public function getComputedPrice(): mixed
{
	$data = new \Pimcore\Model\DataObject\Data\CalculatedValue('computedPrice');
	$data->setContextualData("object", null, null, null);
	$object = $this;
	$data = \Pimcore\Model\DataObject\Service::getCalculatedFieldValue($object, $data);

	return $data;
}

}

