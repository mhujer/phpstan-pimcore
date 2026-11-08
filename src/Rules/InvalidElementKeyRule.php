<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Element\ElementType;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\Document;

/** @implements Rule<Expr> */
final class InvalidElementKeyRule implements Rule
{
    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $call = NamedCall::of($node);
        if ($call === null || $call->node->isFirstClassCallable()) {
            return [];
        }

        $name = strtolower($call->name);
        if (!in_array($name, ['setkey', 'create', 'setvalues', 'setvalue'], true) || ($name === 'create') !== ($call->node instanceof StaticCall)) {
            return [];
        }

        $receiver = $call->receiverType($scope);
        $method = $scope->getMethodReflection($receiver, $call->name);
        if ($method === null) {
            return [];
        }

        $elementType = ElementType::ofReceiver($receiver);
        if ($elementType === null) {
            return [];
        }

        foreach ($receiver->getObjectClassReflections() as $class) {
            if (!$class->hasNativeMethod('setKey') || $class->getNativeMethod('setKey')->getDeclaringClass()->getName() !== $elementType->baseClass()) {
                return [];
            }

            if ($name !== 'setkey' && !ValuesArray::usesNativeSetters($class)) {
                return [];
            }
        }

        if ($name === 'create') {
            $declaring = $method->getDeclaringClass();
            if (!in_array($declaring->getName(), [Asset::class, Document::class, Folder::class], true) && $declaring->getParentClass()?->getName() !== Concrete::class) {
                return [];
            }
        }

        $args = ArgumentsNormalizer::reorderArgs(ParametersAcceptorSelector::selectFromArgs($scope, $call->node->getArgs(), $method->getVariants()), $call->node->getArgs());
        if ($args === null) {
            return [];
        }

        $keys = [];
        if ($name === 'setkey') {
            if (isset($args[0]) && !$args[0]->unpack) {
                $keys[] = $args[0]->value;
            }
        } else {
            $valuesArgs = $name === 'create' && $elementType !== ElementType::Object ? array_slice($args, 1) : $args;
            foreach (ValuesArray::read($name, $valuesArgs, $scope) as [$key, $value]) {
                if (!is_string($key) || strtolower($key) !== 'key') {
                    continue;
                }

                $keys[] = $value;
            }
        }

        $reserved = $elementType->reservedKeyCharacters();
        $errors = [];
        foreach ($keys as $key) {
            foreach (array_merge(...ConstantStringParts::segments($key, $scope, $this->reflectionProvider)) as $string) {
                $invalid = strpbrk($string, $reserved);
                if ($invalid === false) {
                    continue;
                }

                $message = sprintf('Element key contains reserved character "%s" for %s elements.', $invalid[0], $elementType->value);
                $errors[$message] = RuleErrorBuilder::message($message)
                    ->identifier('pimcore.invalidElementKey')
                    ->build();
            }
        }

        return array_values($errors);
    }
}
