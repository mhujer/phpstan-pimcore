<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\DataWithDefinitions;

use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverriddenProduct;
use Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\RedeclaringOverriddenProduct;

use function PHPStan\Testing\assertType;

function selectOnClassOverride(OverriddenProduct $p): void
{
    assertType("array<'green'|'red'>|null", $p->getColors());
    // A getter inherited from the generated class narrows the same as on the generated class.
    assertType("''|'green'|'red'|null", $p->getColor());
    assertType("''|'green'|'red'|null", $p->get('color'));
    assertType("''|'summer'|'winter'|null", $p->getSeason('en'));
}

function selectOnRedeclaringClassOverride(RedeclaringOverriddenProduct $p): void
{
    // The getter still belongs to the generated class's field.
    assertType("''|'green'|'red'|null", $p->getColor());
}
