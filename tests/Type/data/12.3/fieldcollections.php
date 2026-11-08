<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Data\Line123;

use Pimcore\Model\DataObject\Product;

use function PHPStan\Testing\assertType;

function fieldcollectionGetterWithoutClassDefinitions(Product $p): void
{
    // 12.3 declares a bare Fieldcollection; without class definitions the items are any fieldcollection
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData>|null', $p->getVariants());
    assertType('Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData>|null', $p->get('variants'));
}
