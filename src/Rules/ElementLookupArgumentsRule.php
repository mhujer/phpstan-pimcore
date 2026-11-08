<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Element\ElementType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BooleanType;
use PHPStan\Type\NeverType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\Service;
use Pimcore\Model\Element\Tag;

/**
 * @implements Rule<StaticCall>
 */
final class ElementLookupArgumentsRule implements Rule
{
    /**
     * Per declaring class and lowercased method: the position of the element type argument and of the options
     * argument, or null if the method has none.
     */
    private const array ARGUMENT_POSITIONS = [
        Service::class => [
            'getelementbyid' => [0, 2],
            'getelementbypath' => [0, null],
            'pathexists' => [1, null],
            'getvalidkey' => [1, null],
            'isvalidkey' => [1, null],
            'isvalidpath' => [1, null],
            'getelementcachetag' => [0, null],
            'getsessionkey' => [0, null],
            'getelementfromsession' => [0, null],
            'removeelementfromsession' => [0, null],
            'findforbiddenpaths' => [0, null],
        ],
        Tag::class => [
            'gettagsforelement' => [0, null],
            'addtagtoelement' => [0, null],
            'removetagfromelement' => [0, null],
            'settagsforelement' => [0, null],
            'batchassigntagstoelement' => [0, null],
        ],
        AbstractObject::class => ['getbyid' => [null, 1], 'getbypath' => [null, 1]],
        Asset::class => ['getbyid' => [null, 1], 'getbypath' => [null, 1]],
        Document::class => ['getbyid' => [null, 1], 'getbypath' => [null, 1]],
    ];

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Name || !$node->name instanceof Identifier) {
            return [];
        }

        $className = $scope->resolveName($node->class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }

        $class = $this->reflectionProvider->getClass($className);
        $name = $node->name->toString();
        if (!$class->hasNativeMethod($name)) {
            return [];
        }

        $method = $class->getNativeMethod($name);
        $declaringClass = $method->getDeclaringClass()->getName();
        $positions = self::ARGUMENT_POSITIONS[$declaringClass][strtolower($name)] ?? null;
        if ($positions === null) {
            return [];
        }

        [$typePosition, $optionPosition] = $positions;
        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node->getArgs(),
        );
        $optionLabel = ($declaringClass === Service::class ? 'Element\\Service' : substr($className, (int) strrpos($className, '\\') + 1)) . '::' . $method->getName() . '()';
        $errors = $optionPosition === null ? [] : $this->optionErrors($args[$optionPosition] ?? null, $scope, $optionLabel, $declaringClass === AbstractObject::class);
        if ($typePosition === null || !isset($args[$typePosition]) || $args[$typePosition]->unpack) {
            return $errors;
        }

        $typeLabel = ($declaringClass === Service::class ? 'Element\\Service' : 'Tag') . '::' . $method->getName() . '()';
        foreach ($scope->getType($args[$typePosition]->value)->getConstantStrings() as $constant) {
            $value = $constant->getValue();
            if (ElementType::tryFrom($value) !== null) {
                continue;
            }

            $error = RuleErrorBuilder::message(sprintf("%s expects element type 'asset', 'document' or 'object', %s given.", $typeLabel, $constant->describe(VerbosityLevel::value())))
                ->identifier('pimcore.elementType');
            $suggestion = match (strtolower($value)) {
                'asset', 'assets' => 'asset',
                'document', 'documents' => 'document',
                'object', 'objects', 'dataobject', 'dataobjects' => 'object',
                default => null,
            };
            if ($suggestion !== null) {
                $error->tip(sprintf("Did you mean '%s'?", $suggestion));
            }

            $errors[] = $error->build();
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function optionErrors(?Arg $arg, Scope $scope, string $label, bool $nativeBoolShape): array
    {
        if ($arg === null || $arg->unpack || !$arg->value instanceof Array_) {
            return [];
        }

        $errors = [];
        foreach ($arg->value->items as $item) {
            if ($item->unpack || $item->key === null) {
                continue;
            }

            $keyType = $scope->getType($item->key);
            if (!$keyType->isConstantScalarValue()->yes()) {
                continue;
            }

            foreach ($keyType->getConstantScalarValues() as $key) {
                if ($key !== 'force') {
                    $errors[] = RuleErrorBuilder::message(sprintf("%s option %s is not supported: the only option is 'force'.", $label, var_export($key, true)))
                        ->identifier('pimcore.unknownLookupOption')
                        ->build();
                    continue;
                }

                $valueType = $scope->getType($item->value);
                if ($nativeBoolShape || !(TypeCombinator::intersect(new BooleanType(), $valueType) instanceof NeverType)) {
                    continue;
                }

                $errors[] = RuleErrorBuilder::message(sprintf("%s option 'force' expects bool, %s given.", $label, $valueType->describe(VerbosityLevel::typeOnly())))
                    ->identifier('pimcore.lookupOptionType')
                    ->build();
            }
        }

        return $errors;
    }
}
