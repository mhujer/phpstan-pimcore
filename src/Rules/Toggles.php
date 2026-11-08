<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\Rules;

/**
 * The toggle groups {@see ToggleNotRestoredRule} checks: the built-in ones plus the `additionalToggles` parameter. In
 * one function, the first call to a method of a group flips the toggle and any later call to a method of the same
 * group restores it.
 */
final readonly class Toggles
{
    /**
     * The built-in group of object inherited values, the only toggle with a helper that flips it for one callback.
     */
    private const array OBJECT_INHERITED_VALUES = [
        'class' => 'Pimcore\Model\DataObject\AbstractObject',
        'methods' => ['setGetInheritedValues'],
    ];

    /**
     * Matched by the class that declares the method, so `Product::`, `DataObject::` and instance calls all match.
     */
    private const array BUILT_IN = [
        ['class' => 'Pimcore\Model\DataObject\AbstractObject', 'methods' => ['setHideUnpublished']],
        ['class' => 'Pimcore\Model\Document', 'methods' => ['setHideUnpublished']],
        self::OBJECT_INHERITED_VALUES,
        ['class' => 'Pimcore\Model\Document\PageSnippet', 'methods' => ['setGetInheritedValues']],
        ['class' => 'Pimcore\Model\Element\AbstractElement', 'methods' => ['setGetInheritedProperties']],
        ['class' => 'Pimcore\Model\DataObject\Localizedfield', 'methods' => ['setGetFallbackValues']],
        ['class' => 'Pimcore\Model\DataObject\Localizedfield', 'methods' => ['setStrictMode']],
        [
            'class' => 'Pimcore\Model\DataObject\AbstractObject',
            'methods' => ['setDisableDirtyDetection', 'disableDirtyDetection', 'enableDirtyDetection'],
        ],
        ['class' => 'Pimcore\Model\Version', 'methods' => ['disable', 'enable']],
        ['class' => 'Pimcore\Cache', 'methods' => ['disable', 'enable']],
        ['class' => 'Pimcore\Cache\RuntimeCache', 'methods' => ['disable', 'enable']],
        ['class' => 'Pimcore\Model\DataObject\AbstractObject', 'methods' => ['setDoNotRestoreKeyAndPath']],
        ['class' => 'Pimcore\Model\DataObject\Concrete\Dao\InheritanceHelper', 'methods' => ['setUseRuntimeCache']],
        ['class' => 'Pimcore\Cache', 'methods' => ['setForceImmediateWrite']],
        ['class' => 'Pimcore\Mail', 'methods' => ['setForceDebugMode']],
        ['class' => 'Pimcore\Model\DataObject\ClassDefinition\Service', 'methods' => ['setDoRemoveDynamicOptions']],
    ];

    /**
     * Lowercased, as PHP matches class and method names.
     *
     * @var list<array{class: string, methods: list<string>}>
     */
    private array $groups;

    /**
     * @param list<array{class: string, methods: list<string>}> $additionalToggles
     */
    public function __construct(array $additionalToggles)
    {
        $this->groups = array_map(self::lowercase(...), [...self::BUILT_IN, ...$additionalToggles]);
    }

    /**
     * The group a method belongs to, or null if it isn't a toggle method.
     */
    public function groupOf(string $declaringClass, string $method): ?int
    {
        $declaringClass = strtolower($declaringClass);
        $method = strtolower($method);
        foreach ($this->groups as $index => $group) {
            if ($group['class'] === $declaringClass && in_array($method, $group['methods'], true)) {
                return $index;
            }
        }

        return null;
    }

    public function tipFor(int $group): ?string
    {
        if (($this->groups[$group] ?? null) === self::lowercase(self::OBJECT_INHERITED_VALUES)) {
            return 'Use Pimcore\\Model\\DataObject\\Service::useInheritedValues(), which restores the previous value for you.';
        }

        return null;
    }

    /**
     * Whether some group has a method of this name, whatever its class. A cheap check before resolving a call.
     */
    public function isToggleMethodName(string $method): bool
    {
        $method = strtolower($method);
        foreach ($this->groups as $group) {
            if (in_array($method, $group['methods'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{class: string, methods: list<string>} $group
     * @return array{class: string, methods: list<string>}
     */
    private static function lowercase(array $group): array
    {
        return [
            'class' => strtolower($group['class']),
            'methods' => array_map(strtolower(...), $group['methods']),
        ];
    }
}
