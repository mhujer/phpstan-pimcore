<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data;

use Pimcore\Model\DataObject\Objectbrick;
use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function brickContainerGetterIsNonNull(Product $p): void
{
    assertType('Pimcore\Model\DataObject\Product\Attributes', $p->getAttributes());
    assertType('Pimcore\Model\DataObject\Product\Attributes', $p->get('attributes'));
}

function getOnBrickContainerReturnsTheBrick(Product $p, string $name): void
{
    $attributes = $p->getAttributes();
    assertType('Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|null', $attributes->get('Dimensions'));
    assertType('Pimcore\Model\DataObject\Objectbrick\Data\Electrical|null', $attributes->get('electrical'));
    assertType('mixed', $attributes->get($name));
    assertType('mixed', $attributes->get('NoSuchBrick'));
}

function getOnBaseObjectbrickStaysMixed(Objectbrick $bricks): void
{
    assertType('mixed', $bricks->get('Dimensions'));
}

function getItemsListsTheAllowedBricks(Product $p, Objectbrick $bricks): void
{
    $brickList = 'list<Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|Pimcore\Model\DataObject\Objectbrick\Data\Electrical>';
    assertType($brickList, $p->getAttributes()->getItems());
    assertType($brickList, $p->getAttributes()->getItems(true));
    assertType('array', $bricks->getItems());
}

function brickGettersAndBrickFields(Product $p): void
{
    $attributes = $p->getAttributes();
    assertType('Pimcore\Model\DataObject\Objectbrick\Data\Dimensions|null', $attributes->getDimensions());

    $dimensions = $attributes->getDimensions();
    if ($dimensions === null) {
        return;
    }

    assertType('float|null', $dimensions->getWidth());
}
