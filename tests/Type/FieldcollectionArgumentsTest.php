<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Type;

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;
use Mhujer\PHPStan\Pimcore\Tests\Phpstan;
use PHPUnit\Framework\TestCase;

final class FieldcollectionArgumentsTest extends TestCase
{
    public function testMutationsAcceptOnlyTheDeclaredItemTypes(): void
    {
        $project = Phpstan::createProject();
        file_put_contents($project . '/phpstan.neon', sprintf(<<<'NEON'
            includes:
                - %1$s/extension.neon
            parameters:
                level: max
                paths:
                    - %1$s/tests/Type/calls/fieldcollection-items.php
                tmpDir: cache
                bootstrapFiles:
                    - %1$s/tests/bootstrap.php
                pimcore:
                    classDefinitionDirectory: '%2$s'
            NEON, Phpstan::root(), Fixtures::definitionsDirectory()));

        self::assertSame([
            [
                'line' => 19,
                'message' => 'Parameter #1 $item of method Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier>::add() expects Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier, Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry given.',
                'identifier' => 'argument.type',
            ],
            [
                'line' => 28,
                'message' => 'Parameter #1 $item of method Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>::add() expects Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry, Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData given.',
                'identifier' => 'argument.type',
            ],
            [
                'line' => 38,
                'message' => 'Parameter #1 $items of method Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier>::setItems() expects array<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier>, array<int, Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry> given.',
                'identifier' => 'argument.type',
            ],
            [
                'line' => 47,
                'message' => 'Parameter #1 $items of method Pimcore\Model\DataObject\Fieldcollection<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>::setItems() expects array<Pimcore\Model\DataObject\Fieldcollection\Data\PriceTier|Pimcore\Model\DataObject\Fieldcollection\Data\StockEntry>, array<int, Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData> given.',
                'identifier' => 'argument.type',
            ],
        ], Phpstan::analyse($project, true));
    }
}
