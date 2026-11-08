<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Type\FinderMethodsClassReflectionExtension;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Listing\AbstractListing;

/** @implements Rule<Expr> */
final class ListingArgumentsRule implements Rule
{
    public function __construct(private readonly FinderMethodsClassReflectionExtension $finders)
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
        if (!in_array($name, ['setorder', 'setorderkey', 'setgroupby', 'setconditionvariables', 'setobjecttypes', 'getchildren', 'haschildren', 'getsiblings', 'hassiblings', 'getlist', 'setvalues'], true) && !str_starts_with($name, 'getby')) {
            return [];
        }

        $receiver = $call->receiverType($scope);
        $method = $scope->getMethodReflection($receiver, $call->name);
        if ($method === null) {
            return [];
        }

        $class = $receiver->getObjectClassReflections()[0] ?? null;
        $declaring = $class !== null && $class->hasNativeMethod($call->name) ? $class->getNativeMethod($call->name)->getDeclaringClass()->getName() : $method->getDeclaringClass()->getName();
        $finder = $class !== null && !$class->hasNativeMethod($call->name) && $this->finders->hasMethod($class, $call->name);
        if (!$finder && $name === 'getlist') {
            if (!in_array($declaring, [AbstractObject::class, 'Pimcore\\Model\\Asset', 'Pimcore\\Model\\Document'], true)) {
                return [];
            }
        } elseif (!$finder && $name === 'setvalues') {
            if (!(new ObjectType(AbstractListing::class))->isSuperTypeOf($receiver)->yes() || $class === null || !ValuesArray::usesNativeSetters($class)) {
                return [];
            }
        } elseif (!$finder && (in_array($name, ['getchildren', 'haschildren', 'getsiblings', 'hassiblings'], true) ? $declaring !== AbstractObject::class : ($name === 'setobjecttypes' ? $declaring !== 'Pimcore\\Model\\DataObject\\Listing' : $declaring !== AbstractListing::class))) {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(ParametersAcceptorSelector::selectFromArgs($scope, $call->node->getArgs(), $method->getVariants()), $call->node->getArgs());
        if ($args === null) {
            return [];
        }

        $prefix = sprintf('Call to %s::%s()', $receiver->describe(VerbosityLevel::typeOnly()), $method->getName());
        $config = in_array($name, ['getlist', 'setvalues'], true);
        $key = in_array($name, ['getchildren', 'haschildren', 'getsiblings', 'hassiblings'], true) || $finder ? 'objectTypes' : lcfirst(substr($method->getName(), 3));
        $index = 0;
        if ($finder) {
            foreach ($method->getVariants()[0]->getParameters() as $position => $parameter) {
                if ($parameter->getName() === 'objectTypes') {
                    $index = $position;
                    break;
                }
            }
        }

        $entries = $config ? ValuesArray::read($name, $args, $scope) : (isset($args[$index]) && !$args[$index]->unpack ? [[$key, $args[$index]->value]] : []);
        $errors = [];
        foreach ($entries as [$key, $expr]) {
            $config = in_array($name, ['getlist', 'setvalues'], true);
            $label = $prefix . ($config ? " option '$key'" : '');
            $key = is_string($key) ? strtolower($key) : $key;
            if ($key === 'objecttypes') {
                foreach (self::strings($scope->getType($expr)) as $value) {
                    if (in_array($value, ['object', 'variant', 'folder'], true) && (!$finder || $value !== 'folder')) {
                        continue;
                    }

                    $message = $value === 'folder' ? " with object type 'folder', which finders don't support." : sprintf(" with unknown object type '%s'.", $value);
                    $errors[] = RuleErrorBuilder::message($label . $message)->identifier('pimcore.objectType')->build();
                }
            }

            if ($key === 'conditionvariables') {
                $errors[] = RuleErrorBuilder::message($label . ' has no effect; loading the listing overwrites them.')->identifier('pimcore.conditionVariablesIgnored')->tip('Pass the values to setCondition() or addConditionParam().')->build();
            }

            if ($key === 'order' && !$this->customOrders($receiver, $scope)) {
                foreach (self::strings($scope->getType($expr)) as $value) {
                    if ($value === '' || in_array(strtoupper($value), ['ASC', 'DESC'], true)) {
                        continue;
                    }

                    $errors[] = RuleErrorBuilder::message(sprintf("%s with invalid order '%s'; Pimcore accepts only ASC or DESC.", $label, $value))->identifier('pimcore.listingOrder')->build();
                }
            }

            if (!in_array($key, ['orderkey', 'groupby'], true) || (!$config && isset($args[1]) && !$scope->getType($args[1]->value)->isTrue()->yes())) {
                continue;
            }

            foreach (self::strings($scope->getType($expr)) as $value) {
                $parts = $key === 'groupby' ? array_map(trim(...), explode(',', trim($value, '`'))) : [$value];
                foreach ($parts as $part) {
                    if (preg_match('/[\\s(),`\'"]/', $part) !== 1) {
                        continue;
                    }

                    $setter = $key === 'groupby' ? 'setGroupBy' : 'setOrderKey';
                    $tip = $config ? "Call $setter(…, false) on the listing to use it unquoted." : 'Pass false as the second argument to use it unquoted.';
                    $errors[] = RuleErrorBuilder::message(sprintf("%s with expression '%s', which Pimcore quotes as a single column name.", $label, $part))->identifier('pimcore.listingOrderKey')->tip($tip)->build();
                }
            }
        }

        return $errors;
    }

    private function customOrders(Type $receiver, Scope $scope): bool
    {
        return $receiver->hasProperty('validOrders')->yes() && $receiver->getProperty('validOrders', $scope)->getDeclaringClass()->getName() !== AbstractListing::class;
    }

    /** @return list<string> */
    private static function strings(Type $type): array
    {
        $strings = array_map(static fn ($string): string => $string->getValue(), $type->getConstantStrings());
        foreach ($type->getConstantArrays() as $array) {
            foreach ($array->getValueTypes() as $value) {
                array_push($strings, ...self::strings($value));
            }
        }

        return array_values(array_unique($strings));
    }
}
