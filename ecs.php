<?php

declare(strict_types=1);

use SlevomatCodingStandard\Sniffs\Arrays\DisallowImplicitArrayCreationSniff;
use SlevomatCodingStandard\Sniffs\Arrays\TrailingArrayCommaSniff;
use SlevomatCodingStandard\Sniffs\Classes\ClassConstantVisibilitySniff;
use SlevomatCodingStandard\Sniffs\Classes\DisallowMultiConstantDefinitionSniff;
use SlevomatCodingStandard\Sniffs\Classes\DisallowMultiPropertyDefinitionSniff;
use SlevomatCodingStandard\Sniffs\Classes\ModernClassNameReferenceSniff;
use SlevomatCodingStandard\Sniffs\Classes\RequireAbstractOrFinalSniff;
use SlevomatCodingStandard\Sniffs\Classes\RequireConstructorPropertyPromotionSniff;
use SlevomatCodingStandard\Sniffs\Classes\UselessLateStaticBindingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\EmptyCommentSniff;
use SlevomatCodingStandard\Sniffs\Commenting\ForbiddenAnnotationsSniff;
use SlevomatCodingStandard\Sniffs\Commenting\UselessFunctionDocCommentSniff;
use SlevomatCodingStandard\Sniffs\Commenting\UselessInheritDocCommentSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\EarlyExitSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\NewWithParenthesesSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullCoalesceEqualOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireNullCoalesceOperatorSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessIfConditionWithReturnSniff;
use SlevomatCodingStandard\Sniffs\ControlStructures\UselessTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\DeadCatchSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use SlevomatCodingStandard\Sniffs\Exceptions\RequireNonCapturingCatchSniff;
use SlevomatCodingStandard\Sniffs\Functions\ArrowFunctionDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInCallSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInClosureUseSniff;
use SlevomatCodingStandard\Sniffs\Functions\RequireTrailingCommaInDeclarationSniff;
use SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff;
use SlevomatCodingStandard\Sniffs\Functions\StrictCallSniff;
use SlevomatCodingStandard\Sniffs\Functions\UnusedInheritedVariablePassedToClosureSniff;
use SlevomatCodingStandard\Sniffs\Functions\UnusedParameterSniff;
use SlevomatCodingStandard\Sniffs\Functions\UselessParameterDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\AlphabeticallySortedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\DisallowGroupUseSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\MultipleUsesPerLineSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseDoesNotStartWithBackslashSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseFromSameNamespaceSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UselessAliasSniff;
use SlevomatCodingStandard\Sniffs\Operators\DisallowEqualOperatorsSniff;
use SlevomatCodingStandard\Sniffs\Operators\NegationOperatorSpacingSniff;
use SlevomatCodingStandard\Sniffs\Operators\RequireCombinedAssignmentOperatorSniff;
use SlevomatCodingStandard\Sniffs\Operators\SpreadOperatorSpacingSniff;
use SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff;
use SlevomatCodingStandard\Sniffs\PHP\OptimizedFunctionsWithoutUnpackingSniff;
use SlevomatCodingStandard\Sniffs\PHP\ReferenceSpacingSniff;
use SlevomatCodingStandard\Sniffs\PHP\ShortListSniff;
use SlevomatCodingStandard\Sniffs\PHP\TypeCastSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessSemicolonSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ClassConstantTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\LongTypeHintsSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullTypeHintOnLastPositionSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UnionTypeHintFormatSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff;
use SlevomatCodingStandard\Sniffs\Variables\DisallowVariableVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\DuplicateAssignmentToVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withSpacing(indentation: '    ', lineEnding: "\n")
    ->withSkip([
        // Generated by tools/fixture-generator; never edited by hand.
        __DIR__ . '/tests/fixtures',
        // Interpolated condition rule test data: an unpacked sprintf() argument is what the rule has to handle.
        OptimizedFunctionsWithoutUnpackingSniff::class => [
            __DIR__ . '/tests/Rules/data/interpolated-condition-functions.php',
        ],
        // Class overrides that exercise inheritance and late static binding.
        RequireAbstractOrFinalSniff::class => [
            __DIR__ . '/tests/Type/Fixtures/OverriddenProduct.php',
            __DIR__ . '/tests/Type/Fixtures/OverrideCalls.php',
            __DIR__ . '/tests/Type/Fixtures/OverrideConstructions.php',
        ],
    ])
    ->withPreparedSets(psr12: true)
    ->withRules([
        // Arrays
        DisallowImplicitArrayCreationSniff::class,
        TrailingArrayCommaSniff::class,
        // Classes
        ClassConstantVisibilitySniff::class,
        DisallowMultiConstantDefinitionSniff::class,
        DisallowMultiPropertyDefinitionSniff::class,
        ModernClassNameReferenceSniff::class,
        RequireAbstractOrFinalSniff::class,
        RequireConstructorPropertyPromotionSniff::class,
        UselessLateStaticBindingSniff::class,
        // Commenting
        EmptyCommentSniff::class,
        UselessFunctionDocCommentSniff::class,
        UselessInheritDocCommentSniff::class,
        // Control structures
        AssignmentInConditionSniff::class,
        DisallowEmptySniff::class,
        DisallowYodaComparisonSniff::class,
        EarlyExitSniff::class,
        NewWithParenthesesSniff::class,
        RequireNullCoalesceEqualOperatorSniff::class,
        RequireNullCoalesceOperatorSniff::class,
        UselessIfConditionWithReturnSniff::class,
        UselessTernaryOperatorSniff::class,
        // Exceptions
        DeadCatchSniff::class,
        ReferenceThrowableOnlySniff::class,
        RequireNonCapturingCatchSniff::class,
        // Functions
        ArrowFunctionDeclarationSniff::class,
        RequireTrailingCommaInCallSniff::class,
        RequireTrailingCommaInClosureUseSniff::class,
        RequireTrailingCommaInDeclarationSniff::class,
        StaticClosureSniff::class,
        StrictCallSniff::class,
        UnusedInheritedVariablePassedToClosureSniff::class,
        UnusedParameterSniff::class,
        UselessParameterDefaultValueSniff::class,
        // Namespaces
        AlphabeticallySortedUsesSniff::class,
        DisallowGroupUseSniff::class,
        MultipleUsesPerLineSniff::class,
        UseDoesNotStartWithBackslashSniff::class,
        UseFromSameNamespaceSniff::class,
        UselessAliasSniff::class,
        // Operators
        DisallowEqualOperatorsSniff::class,
        NegationOperatorSpacingSniff::class,
        RequireCombinedAssignmentOperatorSniff::class,
        SpreadOperatorSpacingSniff::class,
        // PHP
        DisallowDirectMagicInvokeCallSniff::class,
        OptimizedFunctionsWithoutUnpackingSniff::class,
        ReferenceSpacingSniff::class,
        ShortListSniff::class,
        TypeCastSniff::class,
        UselessParenthesesSniff::class,
        UselessSemicolonSniff::class,
        // Type hints
        ClassConstantTypeHintSniff::class,
        LongTypeHintsSniff::class,
        NullableTypeForNullDefaultValueSniff::class,
        NullTypeHintOnLastPositionSniff::class,
        ParameterTypeHintSniff::class,
        ParameterTypeHintSpacingSniff::class,
        PropertyTypeHintSniff::class,
        ReturnTypeHintSniff::class,
        ReturnTypeHintSpacingSniff::class,
        UnionTypeHintFormatSniff::class,
        UselessConstantTypeHintSniff::class,
        // Variables
        DisallowVariableVariableSniff::class,
        DuplicateAssignmentToVariableSniff::class,
        UnusedVariableSniff::class,
        UselessVariableSniff::class,
        // Whitespace
        DuplicateSpacesSniff::class,
    ])
    ->withConfiguredRule(DeclareStrictTypesSniff::class, [
        'declareOnFirstLine' => false,
        'linesCountBeforeDeclare' => 1,
        'linesCountAfterDeclare' => 1,
        'spacesCountAroundEqualsSign' => 0,
    ])
    ->withConfiguredRule(ReferenceUsedNamesOnlySniff::class, [
        'allowFallbackGlobalFunctions' => true,
        'allowFallbackGlobalConstants' => true,
        'allowFullyQualifiedGlobalClasses' => false,
        'searchAnnotations' => true,
    ])
    ->withConfiguredRule(UnusedUsesSniff::class, [
        'searchAnnotations' => true,
    ])
    ->withConfiguredRule(ForbiddenAnnotationsSniff::class, [
        'forbiddenAnnotations' => ['@author', '@created', '@copyright', '@license', '@package', '@version'],
    ]);
