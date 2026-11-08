<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Element;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;

/**
 * One of Pimcore's three element kinds, named by the strings Pimcore uses for them.
 */
enum ElementType: string
{
    case Asset = 'asset';
    case Document = 'document';
    case Object = 'object';

    /**
     * The element type whose base class the receiver certainly extends, or null if there is none.
     */
    public static function ofReceiver(Type $receiver): ?self
    {
        foreach (self::cases() as $type) {
            if ((new ObjectType($type->baseClass()))->isSuperTypeOf($receiver)->yes()) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The element type whose base class this is exactly, or null if it isn't a base class.
     */
    public static function ofBaseClass(string $class): ?self
    {
        foreach (self::cases() as $type) {
            if ($type->baseClass() === $class) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The class that declares the element's `getById()`, `getByPath()`, `getList()` and `setKey()`.
     *
     * @return class-string
     */
    public function baseClass(): string
    {
        return match ($this) {
            self::Asset => Asset::class,
            self::Document => Document::class,
            self::Object => AbstractObject::class,
        };
    }

    /**
     * The class every element of this type is an instance of, as an element lookup returns it.
     *
     * @return class-string
     */
    public function elementClass(): string
    {
        return match ($this) {
            self::Asset => Asset::class,
            self::Document => Document::class,
            self::Object => DataObject::class,
        };
    }

    /**
     * The characters Pimcore rejects in an element key of this type.
     */
    public function reservedKeyCharacters(): string
    {
        return match ($this) {
            self::Asset => '/#?*:\\<>|"%+',
            self::Document => '/#?*:\\<>|"%&@=;+',
            self::Object => '/<>',
        };
    }
}
