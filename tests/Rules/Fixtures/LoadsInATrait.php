<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules\Fixtures;

use Pimcore\Model\DataObject;

trait LoadsInATrait
{
    /**
     * @param list<int> $ids
     */
    public function load(array $ids): void
    {
        foreach ($ids as $id) {
            DataObject::getById($id);
        }
    }
}
