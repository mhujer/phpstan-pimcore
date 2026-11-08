<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\DataObject\Fieldcollection;

/**
 * A class override of a class override, redeclaring getters of the generated class.
 */
final class RedeclaringOverriddenProduct extends OverriddenProduct
{
    public function getVariants(): ?Fieldcollection
    {
        return parent::getVariants();
    }

    public function getColor(): ?string
    {
        return parent::getColor();
    }

    public function getMargin(): ?float
    {
        $margin = parent::getMargin();

        return is_float($margin) ? $margin : null;
    }
}
