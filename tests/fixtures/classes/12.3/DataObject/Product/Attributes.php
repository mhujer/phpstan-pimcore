<?php

namespace Pimcore\Model\DataObject\Product;

use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;

class Attributes extends \Pimcore\Model\DataObject\Objectbrick {

protected $brickGetters = ['Dimensions','Electrical'];


protected \Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|null $Dimensions = null;

/**
* @return \Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|null
*/
public function getDimensions(bool $includeDeletedBricks = false)
{
	if(!$includeDeletedBricks &&
		isset($this->Dimensions) &&
		$this->Dimensions->getDoDelete()) {
			return null;
	}
	return $this->Dimensions;
}

/**
* @param \Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|null $Dimensions
* @return $this
*/
public function setDimensions(?\Pimcore\Model\DataObject\Objectbrick\Data\Dimensions $Dimensions): static
{
	$this->Dimensions = $Dimensions;
	return $this;
}

protected \Pimcore\Model\DataObject\Objectbrick\Data\Electrical|null $Electrical = null;

/**
* @return \Pimcore\Model\DataObject\Objectbrick\Data\Electrical|null
*/
public function getElectrical(bool $includeDeletedBricks = false)
{
	if(!$includeDeletedBricks &&
		isset($this->Electrical) &&
		$this->Electrical->getDoDelete()) {
			return null;
	}
	return $this->Electrical;
}

/**
* @param \Pimcore\Model\DataObject\Objectbrick\Data\Electrical|null $Electrical
* @return $this
*/
public function setElectrical(?\Pimcore\Model\DataObject\Objectbrick\Data\Electrical $Electrical): static
{
	$this->Electrical = $Electrical;
	return $this;
}

}

