<?php

declare(strict_types=1);

use Pimcore\Model\DataObject\ClassDefinition\CustomLayout;
use Pimcore\Model\DataObject\Product;
use Pimcore\Navigation\Page;

final class CustomOrdersListing extends Product\Listing
{
    /** @var list<string> */
    protected array $validOrders = ['RANDOM'];
}

final class CustomOrderListing extends Product\Listing
{
    public function setOrder(array|string $order): static
    {
        return parent::setOrder($order);
    }
}

function listingExclusions(CustomOrdersListing $orders, CustomOrderListing $override, CustomLayout\Listing $layout, Page $page): void
{
    $orders->setOrder('RANDOM');
    $orders->setValues(['order' => 'RANDOM']);
    $override->setOrder('RANDOM');
    $override->setValues(['order' => 'RANDOM']);
    $layout->setOrder(static fn (): int => 1);
    $page->setOrder(3);
}
