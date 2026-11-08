<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use PhpParser\Node;
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
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Objectbrick;

/**
 * @implements Rule<StaticCall>
 */
final class UnknownDefinitionNameRule implements Rule
{
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
        if (!$node->class instanceof Name || !$node->name instanceof Identifier || $node->isFirstClassCallable()) {
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
        [$prefix, $label] = match (true) {
            $declaringClass === ClassDefinition::class && strtolower($name) === 'getbyname' => ['Pimcore\\Model\\DataObject\\', 'ClassDefinition::getByName()'],
            $declaringClass === Fieldcollection\Definition::class && strtolower($name) === 'getbykey' => ['Pimcore\\Model\\DataObject\\Fieldcollection\\Data\\', 'Fieldcollection\\Definition::getByKey()'],
            $declaringClass === Objectbrick\Definition::class && strtolower($name) === 'getbykey' => ['Pimcore\\Model\\DataObject\\Objectbrick\\Data\\', 'Objectbrick\\Definition::getByKey()'],
            default => [null, null],
        };
        if ($prefix === null) {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(
            ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants()),
            $node->getArgs(),
        );
        if (!isset($args[0]) || $args[0]->unpack) {
            return [];
        }

        $errors = [];
        foreach ($scope->getType($args[0]->value)->getConstantStrings() as $constant) {
            if ($this->reflectionProvider->hasClass($prefix . $constant->getValue())) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('%s name %s has no generated class.', $label, $constant->describe(VerbosityLevel::value())))
                ->identifier('pimcore.unknownDefinitionName')
                ->build();
        }

        return $errors;
    }
}
