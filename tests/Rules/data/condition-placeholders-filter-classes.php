<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Product\Listing;

final class InheritedFilters extends Listing
{
}
final class CustomFilters extends Listing
{
    public function filterBySku(mixed $data, mixed $operator = '='): static
    {
        return $this->filterByCustom($data, $operator);
    }

    public function filterByCustom(mixed $data, mixed $operator = '='): static
    {
        return $this->setCondition($operator, $data);
    }
}
