<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Product\Listing;

function relationFilters(Listing $list, Category $category, string $operator): void
{
    $list->filterByMainCategory($category);
    $list->filterByMainCategory($category, 'IN (?)');
    $list->filterByMainCategory($category, '<>');
    $list->filterByMainCategory(['id' => 1, 'type' => 'object'], '=');
    $list->filterByMainCategory($category, $operator);
    $list->filterByCategories([1, 2]);
    $list->filterByRelated([1, 2]);
    $list->filterByCategories([1, 2], 'IN (?)');
    $list->filterByCategories([[1, 2]], 'IN (?)');
}
