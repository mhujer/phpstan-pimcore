<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures;

use App\Model\Product;

use function PHPStan\Testing\assertType;

class OverrideCalls extends Product
{
    public static function calls(): void
    {
        assertType('Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls|null', self::getBySku('a', 1));
        assertType('static(Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls)|null', static::getBySku('a', 1));
        assertType('list<Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls>', self::getList()->getObjects());
        assertType('list<static(Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls)>', static::getList()->load());
        assertType('Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls|null', OverrideCalls::getBySku('a', 1));
        assertType('App\Model\Product|null', Product::getBySku('a', 1));
        assertType('App\Model\Product|null', parent::getBySku('a', 1));
        assertType('list<Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls>', OverrideCalls::getList()->load());
        assertType('list<App\Model\Product>', Product::getList()->load());
        assertType('list<Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls>', OverrideCalls::getBySku('a')->load());
    }

    public function instanceCalls(): void
    {
        assertType('Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls|null', OverrideCalls::getBySku('a', 1));
        assertType('App\Model\Product|null', Product::getBySku('a', 1));
        assertType('list<Mhujer\PHPStan\Pimcore\Tests\Type\Fixtures\OverrideCalls>', OverrideCalls::getList()->load());
    }
}
