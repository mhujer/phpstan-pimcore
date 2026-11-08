<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\Type\GeneratedGetters;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/** @implements Rule<Expr> */
final class UnknownValuesKeyRule implements Rule
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
        if (!in_array($name, ['create', 'getlist', 'setvalues', 'setvalue'], true) || in_array($name, ['create', 'getlist'], true) !== ($call->node instanceof StaticCall)) {
            return [];
        }

        $receiver = $call->receiverType($scope);
        $method = $scope->getMethodReflection($receiver, $call->name);
        if ($method === null) {
            return [];
        }

        $args = ArgumentsNormalizer::reorderArgs(ParametersAcceptorSelector::selectFromArgs($scope, $call->node->getArgs(), $method->getVariants()), $call->node->getArgs());
        if ($args === null) {
            return [];
        }

        $errors = [];
        foreach ($receiver->getObjectClassReflections() as $class) {
            if (!$this->supportedModel($class) || !$this->standardValues($class)) {
                continue;
            }

            if (!$call->node instanceof StaticCall && !$class->isFinal() && GeneratedGetters::of(new ObjectType($class->getName())) === null && !$this->generatedListing($class)) {
                continue;
            }

            $asset = $class->getName() === 'Pimcore\\Model\\Asset' || $class->isSubclassOf('Pimcore\\Model\\Asset');
            $document = $class->getName() === 'Pimcore\\Model\\Document' || $class->isSubclassOf('Pimcore\\Model\\Document');
            if ($name === 'create' && !$asset && !$document && $class->getName() !== 'Pimcore\\Model\\DataObject\\Folder' && GeneratedGetters::of(new ObjectType($class->getName())) === null) {
                continue;
            }

            $entries = ValuesArray::read($name, $name === 'create' && ($asset || $document) ? array_slice($args, 1) : $args, $scope);
            $targets = [$class];
            if ($name === 'getlist') {
                $base = $asset ? 'Pimcore\\Model\\Asset' : ($document ? 'Pimcore\\Model\\Document' : (GeneratedGetters::of(new ObjectType($class->getName())) !== null ? $class->getName() : 'Pimcore\\Model\\DataObject'));
                $listingNames = [$base . '\\Listing'];
                foreach ($entries as [$key, $value]) {
                    if ($key !== 'class') {
                        continue;
                    }

                    $type = $scope->getType($value);
                    if (!$type->isConstantScalarValue()->yes()) {
                        continue 2;
                    }

                    if ($asset || $document) {
                        continue;
                    }

                    $listingNames = [];
                    foreach ($type->getConstantScalarValues() as $selected) {
                        if (is_string($selected) && $selected !== '' && $selected !== '0') {
                            $listingNames[] = ltrim($selected, '\\') . '\\Listing';
                        } else {
                            $listingNames[] = $base . '\\Listing';
                        }
                    }
                }

                $targets = [];
                foreach (array_unique($listingNames) as $listing) {
                    if (!$this->reflectionProvider->hasClass($listing)) {
                        continue 2;
                    }

                    $targets[] = $this->reflectionProvider->getClass($listing);
                }
            } elseif ($name === 'create' && $asset) {
                foreach (['Folder', 'Image', 'Audio', 'Video', 'Document', 'Text', 'Archive', 'Unknown'] as $subclass) {
                    $assetClass = 'Pimcore\\Model\\Asset\\' . $subclass;
                    if (!$this->reflectionProvider->hasClass($assetClass)) {
                        continue;
                    }

                    $targets[] = $this->reflectionProvider->getClass($assetClass);
                }
            }

            foreach ($entries as [$key]) {
                if (is_string($key) && (strtolower($key) === 'id' || $name === 'getlist' && $key === 'class')) {
                    continue;
                }

                $prefix = sprintf('Call to %s::%s()', $class->getName(), $method->getName());
                $special = true;
                if (is_int($key)) {
                    $message = "$prefix with integer key $key; Pimcore ignores it.";
                } elseif (strtolower($key) === 'value') {
                    $message = "$prefix with key '$key', which Pimcore always ignores.";
                } elseif (str_starts_with($key, 'o_')) {
                    $message = "$prefix with key '$key', which triggers Pimcore's legacy compatibility branch and throws BadMethodCallException.";
                } else {
                    if (array_filter($targets, static fn (ClassReflection $target): bool => $target->hasNativeMethod('set' . $key)) !== []) {
                        continue;
                    }

                    $special = false;
                    $message = $name === 'getlist'
                        ? sprintf("getList() option '%s' has no setter on %s; Pimcore ignores it.", $key, $targets[0]->getName())
                        : sprintf("%s with key '%s', but %s has no set%s(); Pimcore ignores it.", $prefix, $key, $class->getName(), ucfirst($key));
                }

                $error = RuleErrorBuilder::message($message)->identifier('pimcore.unknownValuesKey');
                $tips = [];
                if (!$special) {
                    foreach ($targets as $target) {
                        foreach ($target->getNativeReflection()->getMethods() as $setter) {
                            if (!str_starts_with(strtolower($setter->getName()), 'set') || levenshtein(strtolower((string) $key), strtolower(substr($setter->getName(), 3))) > 2) {
                                continue;
                            }

                            $tips[strtolower($setter->getName())] = lcfirst(substr($setter->getName(), 3));
                        }
                    }
                }

                if (count($tips) === 1) {
                    $error = $error->tip("Did you mean '" . array_values($tips)[0] . "'?");
                }

                $errors[] = $error->build();
            }
        }

        return $errors;
    }

    private function generatedListing(ClassReflection $class): bool
    {
        $name = $class->getName();
        if (!str_ends_with($name, '\\Listing')) {
            return false;
        }

        $owner = substr($name, 0, -strlen('\\Listing'));

        return $class->isSubclassOf('Pimcore\\Model\\DataObject\\Listing') && $this->reflectionProvider->hasClass($owner) && GeneratedGetters::of(new ObjectType($owner)) !== null;
    }

    private function supportedModel(ClassReflection $class): bool
    {
        if (GeneratedGetters::of(new ObjectType($class->getName())) !== null) {
            return true;
        }

        foreach (['Pimcore\\Model\\DataObject\\AbstractObject', 'Pimcore\\Model\\Asset', 'Pimcore\\Model\\Document', 'Pimcore\\Model\\DataObject\\Listing', 'Pimcore\\Model\\Asset\\Listing', 'Pimcore\\Model\\Document\\Listing'] as $base) {
            if ($class->getName() === $base || $class->isSubclassOf($base)) {
                return true;
            }
        }

        return false;
    }

    private function standardValues(ClassReflection $class): bool
    {
        foreach (['setValues', 'setValue'] as $method) {
            if (!$class->hasNativeMethod($method) || $class->getNativeMethod($method)->getDeclaringClass()->getName() !== 'Pimcore\\Model\\AbstractModel') {
                return false;
            }
        }

        return true;
    }
}
