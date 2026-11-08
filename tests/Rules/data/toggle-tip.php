<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Data\ToggleTip;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

function objectInheritedValues(): void
{
    DataObject::setGetInheritedValues(true);
}

function objectInheritedValuesRestoredOutsideFinally(): void
{
    DataObject::setGetInheritedValues(true);
    DataObject::setGetInheritedValues(false);
}

function documentInheritedValuesHaveNoTip(): void
{
    Document\PageSnippet::setGetInheritedValues(true);
}
