<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Tests;

use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPStan\PhpDoc\Tag\MethodTag;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Listing;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Listing\AbstractListing;
use ReflectionClass;
use RuntimeException;

/**
 * A stub's class PHPDoc replaces Pimcore's, so our stubs declare Pimcore's class-level `@method` tags again, in our
 * own form. This fails when they no longer match the installed Pimcore's, e.g. after a new Pimcore release. The tags
 * are compared by what they mean (name, static, parameters, return type), not by their text.
 */
final class StubMethodTagsTest extends PHPStanTestCase
{
    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function stubs(): iterable
    {
        yield 'AbstractListing' => [AbstractListing::class, __DIR__ . '/../stubs/Listing.stub'];
        yield 'DataObjectListing' => [Listing::class, __DIR__ . '/../stubs/Listing.stub'];
        yield 'ConcreteListing' => [Concrete::class, __DIR__ . '/../stubs/Listing.stub'];
        yield 'Fieldcollection' => [Fieldcollection::class, __DIR__ . '/../stubs/Fieldcollection.stub'];
        yield 'Localizedfield' => [Localizedfield::class, __DIR__ . '/../stubs/Localizedfield.stub'];
        yield 'ObjectMetadata' => [ObjectMetadata::class, __DIR__ . '/../stubs/RelationMetadata.stub'];
        yield 'ElementMetadata' => [ElementMetadata::class, __DIR__ . '/../stubs/RelationMetadata.stub'];
    }

    /**
     * @param class-string $className
     */
    #[DataProvider('stubs')]
    public function testStubDeclaresPimcoresMethodTags(string $className, string $stubFile): void
    {
        $class = new ReflectionClass($className);
        $pimcoreFile = $class->getFileName();
        $pimcoreDocComment = $class->getDocComment();
        self::assertIsString($pimcoreFile);
        self::assertIsString($pimcoreDocComment);

        $pimcoreTags = self::methodTags($pimcoreFile, $className, $pimcoreDocComment);
        $stubTags = self::methodTags($stubFile, $className, self::stubDocComment($stubFile, $className));

        self::assertSame(
            self::describeMethodTags($pimcoreTags, $pimcoreTags),
            self::describeMethodTags($stubTags, $pimcoreTags),
            sprintf('The @method tags in %s no longer match those of %s.', basename($stubFile), $className),
        );
    }

    /**
     * The method tags of a class PHPDoc, resolved in the context of their file.
     *
     * @return array<string, array{string, MethodTag}> name and tag, by lowercase method name, sorted
     */
    private static function methodTags(string $file, string $className, string $docComment): array
    {
        $phpDoc = self::getContainer()->getByType(FileTypeMapper::class)
            ->getResolvedPhpDoc($file, $className, null, null, $docComment);

        $tags = [];
        foreach ($phpDoc->getMethodTags() as $name => $tag) {
            $tags[strtolower($name)] = [$name, $tag];
        }

        ksort($tags);

        return $tags;
    }

    /**
     * Writes the tags in one form for comparison. Descriptions alone depend on how a type was written (shape key
     * order, `array` vs `array<mixed>`), so a type that is equivalent to the reference's type at the same place is
     * written as the reference's.
     *
     * @param array<string, array{string, MethodTag}> $tags
     * @param array<string, array{string, MethodTag}> $reference
     * @return array<string, string>
     */
    private static function describeMethodTags(array $tags, array $reference): array
    {
        $descriptions = [];
        foreach ($tags as $key => [$name, $tag]) {
            $referenceTag = ($reference[$key] ?? null)[1] ?? null;
            $referenceParameters = array_values($referenceTag?->getParameters() ?? []);

            $parameters = [];
            foreach (array_values($tag->getParameters()) as $i => $parameter) {
                $referenceParameter = $referenceParameters[$i] ?? null;
                $parameters[] = sprintf(
                    '%s %s%s$%s%s',
                    self::describeType($parameter->getType(), $referenceParameter?->getType()),
                    $parameter->passedByReference()->yes() ? '&' : '',
                    $parameter->isVariadic() ? '...' : '',
                    array_keys($tag->getParameters())[$i],
                    $parameter->isOptional()
                        ? ' = ' . self::describeType($parameter->getDefaultValue(), $referenceParameter?->getDefaultValue())
                        : '',
                );
            }

            $descriptions[$key] = sprintf(
                '%s%s %s(%s)',
                $tag->isStatic() ? 'static ' : '',
                self::describeType($tag->getReturnType(), $referenceTag?->getReturnType()),
                $name,
                implode(', ', $parameters),
            );
        }

        return $descriptions;
    }

    private static function describeType(?Type $type, ?Type $reference): string
    {
        if ($type === null) {
            return '(none)';
        }

        // Generic Listing tags preserve Pimcore's contract at their declared bounds.
        $type = TypeTraverser::map($type, static fn (Type $inner, callable $traverse): Type => $inner instanceof TemplateType ? $inner->getBound() : $traverse($inner));
        if ($reference !== null && $type->isSuperTypeOf($reference)->yes() && $reference->isSuperTypeOf($type)->yes()) {
            $type = $reference;
        }

        return $type->describe(VerbosityLevel::precise());
    }

    private static function stubDocComment(string $stubFile, string $className): string
    {
        $code = file_get_contents($stubFile);
        if ($code === false) {
            throw new RuntimeException(sprintf('Cannot read %s.', $stubFile));
        }

        $statements = (new ParserFactory())->createForHostVersion()->parse($code) ?? [];
        $statements = (new NodeTraverser(new NameResolver()))->traverse($statements);
        foreach ((new NodeFinder())->findInstanceOf($statements, Class_::class) as $class) {
            $docComment = $class->getDocComment();
            if ($docComment !== null && $class->namespacedName?->toString() === $className) {
                return $docComment->getText();
            }
        }

        throw new RuntimeException(sprintf('No PHPDoc for %s in %s.', $className, $stubFile));
    }
}
