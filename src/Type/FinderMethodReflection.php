<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Type;

use PHPStan\PhpDoc\Tag\MethodTag;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * A finder as its `@method` tag declares it, except for the wider `$limit`. See
 * {@see FinderMethodsClassReflectionExtension}.
 */
final class FinderMethodReflection implements MethodReflection
{
    public const string LIMIT = 'limit';

    /**
     * @var list<ParametersAcceptor>|null
     */
    private ?array $variants = null;

    public function __construct(
        private readonly ClassReflection $declaringClass,
        private readonly string $name,
        private readonly MethodTag $tag,
        private readonly ?Type $throwType,
    ) {
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return true;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this;
    }

    /**
     * @return list<ParametersAcceptor>
     */
    public function getVariants(): array
    {
        if ($this->variants !== null) {
            return $this->variants;
        }

        $parameters = [];
        $isVariadic = false;
        foreach ($this->tag->getParameters() as $name => $parameter) {
            $type = $name === self::LIMIT ? self::widenLimit($parameter->getType()) : $parameter->getType();
            $parameters[] = new FinderParameterReflection($name, $type, $parameter);
            $isVariadic = $parameter->isVariadic();
        }

        return $this->variants = [new FunctionVariant(TemplateTypeMap::createEmpty(), null, $parameters, $isVariadic, $this->tag->getReturnType())];
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getThrowType(): ?Type
    {
        return $this->throwType;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }

    /**
     * Pimcore passes a scalar limit to the Listing's `setLimit(?int)` without strict types, so a bool or a numeric
     * string is cast; an array is merged into the Listing's config.
     */
    private static function widenLimit(Type $declaredType): Type
    {
        return TypeCombinator::union(
            $declaredType,
            new BooleanType(),
            new IntersectionType([new StringType(), new AccessoryNumericStringType()]),
            new ArrayType(new StringType(), new MixedType()),
        );
    }
}
