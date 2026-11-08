<?php

declare(strict_types=1);

namespace ValuesInstance;

use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\Data\Link;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Document;
use Pimcore\Model\User;

function check(Product $product, Product\Listing $listing, Document $document): void
{
    $product->setValues(['completelyUnknown' => 'x', 'sku' => 'x']);
    $product->setValue('completelyUnknown', 'x');
    $listing->setValues(['limitt' => 10, 'limit' => 10]);
    $listing->setValue('limitt', 10);
    $document->setValues(['completelyUnknown' => 'x']);
    $document->setValue('completelyUnknown', 'x');
}

/** @method setGhost(string $value) */
final class ExactDocument extends Document
{
    protected function setSecret(string $value): void
    {
        $this->setKey($value);
    }
}

function exact(ExactDocument $document): void
{
    $document->setValues(['completelyUnknown' => 'x', 'secret' => 'x']);
    $document->setValue('ghost', 'x');
}

final class DifferentValues extends Product
{
    /** @param array<string, mixed> $data */
    public function setValues(array $data = [], bool $ignoreEmptyValues = false): static
    {
        return parent::setValues($data, $ignoreEmptyValues);
    }
}

function excluded(DifferentValues $product, Input $data, Link $link, User $user): void
{
    $product->setValues(['completelyUnknown' => 'x']);
    $product->setValue('completelyUnknown', 'x');
    $data->setValues(['completelyUnknown' => 'x']);
    $link->setValues(['completelyUnknown' => 'x']);
    $user->setValues(['completelyUnknown' => 'x']);
}
