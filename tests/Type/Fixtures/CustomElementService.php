<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

final class CustomElementService extends Service
{
    public static function cloneMe(ElementInterface $element): ElementInterface
    {
        return $element;
    }

    public static function getElementType(ElementInterface $element): ?string
    {
        return $element->getKey();
    }

    public static function getElementTypeByClassName(string $className): ?string
    {
        return $className;
    }
}
