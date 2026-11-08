<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\UnknownDefinitionNameRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnknownDefinitionNameRule>
 */
final class UnknownDefinitionNameRuleTest extends RuleTestCase
{
    public function testUnknownClassDefinitionName(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-definition-names.php'], [
            ["ClassDefinition::getByName() name 'MissingProduct' has no generated class.", 11],
        ]);
    }

    public function testUnknownFieldcollectionAndObjectbrickKeys(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-definition-keys.php'], [
            ["Fieldcollection\\Definition::getByKey() name 'MissingCollection' has no generated class.", 12],
            ["Objectbrick\\Definition::getByKey() name 'MissingBrick' has no generated class.", 13],
        ]);
    }

    public function testConstantUnionsAndNamedArguments(): void
    {
        $this->analyse([__DIR__ . '/data/unknown-definition-inputs.php'], [
            ["ClassDefinition::getByName() name 'MissingNamed' has no generated class.", 14],
            ["ClassDefinition::getByName() name 'MissingCategory' has no generated class.", 15],
        ]);
    }

    public function testDiagnosticIdentifier(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/data/unknown-definition-keys.php']);

        self::assertSame(
            ['pimcore.unknownDefinitionName', 'pimcore.unknownDefinitionName'],
            array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors),
        );
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(UnknownDefinitionNameRule::class);
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
