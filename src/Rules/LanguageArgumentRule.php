<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlCache;
use Mhujer\PHPStan\Pimcore\ContainerXml\ContainerXmlReader;
use Mhujer\PHPStan\Pimcore\Type\GeneratedGetters;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\DependencyTracker;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Pimcore\Model\DataObject\Listing\Concrete as ConcreteListing;
use Pimcore\Model\DataObject\Localizedfield;

/** @implements Rule<Expr> */
final class LanguageArgumentRule implements Rule
{
    public function __construct(private readonly ContainerXmlReader $reader)
    {
    }

    public function getNodeType(): string
    {
        return Expr::class;
    }

    private function trackConfig(Scope $scope): void
    {
        if (!($scope instanceof DependencyTracker)) {
            return;
        }

        $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('parameter', 'pimcore.config', 'config_location', 'system_settings', 'read_target', 'type'));
        $scope->trackValueDependency(ContainerXmlCache::class, ContainerXmlCache::key('parameter', 'pimcore.config', 'general', 'valid_languages'));
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $call = NamedCall::of($node);
        if ($call === null || (!$node instanceof MethodCall && !$node instanceof StaticCall) || $call->node->isFirstClassCallable()) {
            return [];
        }
        $this->trackConfig($scope);
        // Without a read target, Pimcore reads system settings from the container before the settings store.
        $readTarget = $this->reader->config(['config_location', 'system_settings', 'read_target', 'type']);
        if ($readTarget !== null && $readTarget !== 'symfony-config') {
            return [];
        }
        $languages = $this->reader->config(['general', 'valid_languages']);
        if (!is_array($languages) || $languages === []) {
            return [];
        }
        $type = $call->receiverType($scope);
        $method = $scope->getMethodReflection($type, $call->name);
        if ($method === null) {
            return [];
        }
        $variant = ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $method->getVariants());
        $normalized = $node instanceof StaticCall
            ? ArgumentsNormalizer::reorderStaticCallArguments($variant, $node)
            : ArgumentsNormalizer::reorderMethodArguments($variant, $node);
        if ($normalized === null) {
            return [];
        }
        $name = strtolower($call->name);
        $getters = GeneratedGetters::of($type);
        $index = null;
        if (!$node instanceof StaticCall && (new ObjectType(Localizedfield::class))->isSuperTypeOf($type)->yes()) {
            $index = match ($name) {
                'getlocalizedvalue' => 1,
                'setlocalizedvalue' => 2,
                default => null,
            };
        } elseif (!$node instanceof StaticCall && $name === 'setlocale' && (new ObjectType(ConcreteListing::class))->isSuperTypeOf($type)->yes()) {
            $index = 0;
        } elseif ($getters !== null) {
            if (in_array($name, ['get', 'set', 'getbylocalizedfields'], true)) {
                $fieldArg = $normalized->getArgs()[0] ?? null;
                $fields = $fieldArg === null || $fieldArg->unpack ? [] : $scope->getType($fieldArg->value)->getConstantStrings();
                if ($fields === []) {
                    return [];
                }
                foreach ($fields as $field) {
                    $fieldName = $field->getValue();
                    if ($name !== 'getbylocalizedfields') {
                        // Generic get()/set() dispatch through PHP's case-insensitive method lookup.
                        $getter = $scope->getMethodReflection($type, 'get' . ucfirst($fieldName));
                        if ($getter === null) {
                            return [];
                        }
                        $fieldName = lcfirst(substr($getter->getName(), 3));
                    }
                    if (!$getters->isExactLocalizedField($fieldName, $scope)) {
                        return [];
                    }
                }
                $index = $name === 'get' ? 1 : 2;
            } else {
                $prefix = $node instanceof StaticCall ? 'getby' : (str_starts_with($name, 'get') ? 'get' : 'set');
                if (!str_starts_with($name, $prefix)) {
                    return [];
                }
                $getter = $scope->getMethodReflection($type, 'get' . substr($call->name, strlen($prefix)));
                if ($getter === null || !$getters->isExactLocalizedField(lcfirst(substr($getter->getName(), 3)), $scope)) {
                    return [];
                }
                $index = $prefix === 'get' ? 0 : 1;
            }
        }
        $argument = $index === null ? null : ($normalized->getArgs()[$index] ?? null);
        if ($argument === null || $argument->unpack) {
            return [];
        }
        $errors = [];
        foreach ($scope->getType($argument->value)->getConstantStrings() as $language) {
            if ($language->getValue() === '' || in_array($language->getValue(), $languages, true)) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf("Language '%s' is not configured in general.valid_languages.", $language->getValue()))
                ->identifier('pimcore.invalidLanguage')->build();
        }

        return $errors;
    }
}
