<?php

declare(strict_types=1);

use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\SetValueOnlyListing;
use Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures\SetValuesOnlyListing;

function configOverrides(SetValueOnlyListing $value, SetValuesOnlyListing $values, string $name): void
{
    $value->setValues(['condition' => 'name = ' . $name]);
    $value->setValue('condition', 'name = ' . $name);
    $values->setValues(['condition' => 'name = ' . $name]);
    $values->setValue('condition', 'name = ' . $name);
    $value->setValues(['order' => 'RANDOM', 'orderKey' => 'RAND()', 'conditionVariables' => [1], 'objectTypes' => ['invalid']]);
    $values->setValues(['order' => 'RANDOM']);
}
