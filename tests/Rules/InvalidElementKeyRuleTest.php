<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\InvalidElementKeyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/** @extends RuleTestCase<InvalidElementKeyRule> */
final class InvalidElementKeyRuleTest extends RuleTestCase
{
    public function testReservedKeyCharacters(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-element-keys-basic.php'], [
            ['Element key contains reserved character "/" for asset elements.', 13],
            ['Element key contains reserved character "?" for document elements.', 14],
            ['Element key contains reserved character "<" for object elements.', 15],
            ['Element key contains reserved character "#" for asset elements.', 16],
            ['Element key contains reserved character "&" for document elements.', 17],
        ]);
    }

    public function testConstantKeyParts(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-element-keys.php'], [
            ['Element key contains reserved character "/" for asset elements.', 13],
            ['Element key contains reserved character "?" for document elements.', 14],
            ['Element key contains reserved character "<" for object elements.', 15],
            ['Element key contains reserved character "#" for asset elements.', 16],
            ['Element key contains reserved character "&" for document elements.', 17],
            ['Element key contains reserved character "/" for asset elements.', 27],
            ['Element key contains reserved character "?" for document elements.', 28],
            ['Element key contains reserved character "<" for object elements.', 29],
            ['Element key contains reserved character "/" for asset elements.', 30],
        ]);
    }

    public function testValuesArrayKeys(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-element-key-values.php'], [
            ['Element key contains reserved character "/" for object elements.', 15],
            ['Element key contains reserved character "?" for asset elements.', 16],
            ['Element key contains reserved character "&" for document elements.', 17],
            ['Element key contains reserved character "<" for object elements.', 18],
            ['Element key contains reserved character "/" for object elements.', 19],
            ['Element key contains reserved character "?" for asset elements.', 20],
            ['Element key contains reserved character "@" for document elements.', 21],
            ['Element key contains reserved character "/" for object elements.', 22],
        ]);
    }

    public function testKeyFormatLiterals(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-element-key-formats.php'], [
            ['Element key contains reserved character "/" for asset elements.', 12],
            ['Element key contains reserved character "?" for document elements.', 13],
            ['Element key contains reserved character "%" for asset elements.', 16],
        ]);
    }

    public function testCustomMethodsAreSkipped(): void
    {
        $this->analyse([__DIR__ . '/data/invalid-element-key-custom.php'], []);
    }

    public function testDiagnosticIdentifier(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/invalid-element-keys-basic.php']);
        self::assertNotEmpty($errors);
        foreach ($errors as $error) {
            self::assertSame('pimcore.invalidElementKey', $error->getIdentifier());
        }
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(InvalidElementKeyRule::class);
    }

    /** @return list<string> */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
