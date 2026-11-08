<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests\Rules;

use Mhujer\PHPStan\Pimcore\Rules\ElementLookupArgumentsRule;
use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ElementLookupArgumentsRule>
 */
final class ElementLookupArgumentsRuleTest extends RuleTestCase
{
    public function testInvalidElementTypes(): void
    {
        $this->analyse([__DIR__ . '/data/element-lookup-types.php'], [
            ["Element\\Service::getElementById() expects element type 'asset', 'document' or 'object', 'dataobject' given.", 10, "Did you mean 'object'?"],
            ["Element\\Service::getElementByPath() expects element type 'asset', 'document' or 'object', 'Object' given.", 11, "Did you mean 'object'?"],
            ["Element\\Service::pathExists() expects element type 'asset', 'document' or 'object', 'objects' given.", 12, "Did you mean 'object'?"],
            ["Tag::addTagToElement() expects element type 'asset', 'document' or 'object', 'dataObject' given.", 13, "Did you mean 'object'?"],
            ["Tag::getTagsForElement() expects element type 'asset', 'document' or 'object', 'assets' given.", 14, "Did you mean 'asset'?"],
            ["Tag::removeTagFromElement() expects element type 'asset', 'document' or 'object', 'documents' given.", 15, "Did you mean 'document'?"],
            ["Tag::setTagsForElement() expects element type 'asset', 'document' or 'object', 'wrong' given.", 16],
            ["Tag::batchAssignTagsToElement() expects element type 'asset', 'document' or 'object', 'Object' given.", 17, "Did you mean 'object'?"],
            ["Element\\Service::getElementById() expects element type 'asset', 'document' or 'object', 'ASSET' given.", 18, "Did you mean 'asset'?"],
            ["Element\\Service::getElementById() expects element type 'asset', 'document' or 'object', 'objects' given.", 25, "Did you mean 'object'?"],
            ["Element\\Service::getValidKey() expects element type 'asset', 'document' or 'object', 'folder' given.", 31],
            ["Element\\Service::isValidKey() expects element type 'asset', 'document' or 'object', 'folder' given.", 32],
        ]);
    }

    public function testExtendedElementTypes(): void
    {
        $this->analyse([__DIR__ . '/data/element-lookup-extended.php'], [
            ["Element\\Service::isValidPath() expects element type 'asset', 'document' or 'object', 'objects' given.", 10, "Did you mean 'object'?"],
            ["Element\\Service::getElementCacheTag() expects element type 'asset', 'document' or 'object', 'objects' given.", 11, "Did you mean 'object'?"],
            ["Element\\Service::getSessionKey() expects element type 'asset', 'document' or 'object', 'objects' given.", 12, "Did you mean 'object'?"],
            ["Element\\Service::getElementFromSession() expects element type 'asset', 'document' or 'object', 'objects' given.", 13, "Did you mean 'object'?"],
            ["Element\\Service::removeElementFromSession() expects element type 'asset', 'document' or 'object', 'objects' given.", 14, "Did you mean 'object'?"],
            ["Element\\Service::findForbiddenPaths() expects element type 'asset', 'document' or 'object', 'objects' given.", 15, "Did you mean 'object'?"],
        ]);
    }

    public function testLiteralLookupOptions(): void
    {
        $this->analyse([__DIR__ . '/data/element-lookup-options.php'], [
            ["Product::getById() option 'froce' is not supported: the only option is 'force'.", 15],
            ["Asset::getByPath() option 'force' expects bool, int given.", 16],
            ["Element\\Service::getElementById() option 'force' expects bool, string given.", 17],
            ["Page::getById() option 'cache' is not supported: the only option is 'force'.", 18],
            ["DataObject::getByPath() option 'Force' is not supported: the only option is 'force'.", 19],
            ["Document::getByPath() option 'force' expects bool, null given.", 20],
            ["Asset::getById() option 'force' expects bool, array given.", 21],
            ["Element\\Service::getElementById() option 'froce' is not supported: the only option is 'force'.", 22],
            ["Asset::getById() option 'force' expects bool, string given.", 23],
            ["Folder::getById() option 'cache' is not supported: the only option is 'force'.", 24],
            ["Image::getById() option 'cache' is not supported: the only option is 'force'.", 25],
        ]);
    }

    public function testDiagnosticIdentifiers(): void
    {
        $errors = $this->gatherAnalyserErrors([
            __DIR__ . '/data/element-lookup-types.php',
            __DIR__ . '/data/element-lookup-options.php',
        ]);
        $identifiers = array_values(array_unique(array_map(static fn (Error $error): ?string => $error->getIdentifier(), $errors)));
        sort($identifiers);

        self::assertSame(['pimcore.elementType', 'pimcore.lookupOptionType', 'pimcore.unknownLookupOption'], $identifiers);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ElementLookupArgumentsRule::class);
    }

    /**
     * @return list<string>
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/../../rules.neon'];
    }
}
