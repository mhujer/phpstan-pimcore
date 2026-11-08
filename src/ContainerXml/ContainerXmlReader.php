<?php

declare(strict_types=1);

namespace Mhujer\PHPStan\Pimcore\ContainerXml;

use InvalidArgumentException;
use PHPStan\DependencyInjection\Container;
use SimpleXMLElement;

/**
 * @phpstan-type Service array{class: ?string, alias: ?string, abstract: bool, factory: bool, arguments: array<array-key, mixed>}
 */
final class ContainerXmlReader
{
    /**
     * Schema validation happens before PHPStan builds feature services. Reuse its parsed XML at runtime.
     * A new schema validation replaces the entry, so a subsequent analysis in the same process sees fresh config.
     * @var array<string, SimpleXMLElement>
     */
    private static array $validatedXml = [];

    private ?SimpleXMLElement $xml = null;

    private bool $loaded = false;

    /** @var array<string, Service>|null */
    private ?array $services = null;

    /** @var list<array<array-key, mixed>>|null */
    private ?array $listeners = null;

    private readonly string $source;

    private ?string $disabledReason = null;

    public function __construct(private ?string $path, ?Container $container = null)
    {
        if ($path !== null) {
            $this->source = 'own';
            return;
        }
        $symfony = $container !== null && $container->hasParameter('symfony') ? $container->getParameter('symfony') : null;
        $borrowed = is_array($symfony) ? ($symfony['containerXmlPath'] ?? null) : null;
        $this->path = is_string($borrowed) ? $borrowed : null;
        $this->source = $this->path !== null ? 'symfony' : 'none';
    }

    public static function isValidParameter(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        if (!is_string($value) || !is_file($value)) {
            return false;
        }
        unset(self::$validatedXml[$value]);
        $reader = new self($value);
        $reader->load();
        if ($reader->xml !== null) {
            self::$validatedXml[$value] = $reader->xml;
        }

        return true;
    }

    public function diagnosis(): string
    {
        $this->load();

        return 'Pimcore Container XML: ' . ($this->path === null ? 'none' : '"' . $this->path . '"')
            . ' (source: ' . $this->source . ($this->disabledReason === null ? '' : '; disabled: ' . $this->disabledReason) . ').';
    }

    /** @param list<string> $path */
    public function config(array $path): mixed
    {
        return $this->parameter('pimcore.config', $path);
    }

    /** @param list<string> $path */
    public function parameter(string $name, array $path = []): mixed
    {
        $this->load();
        foreach ($this->xml?->parameters->parameter ?? [] as $parameter) {
            if ((string) $parameter['key'] !== $name) {
                continue;
            }
            $value = self::decode($parameter);
            foreach ($path as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    return null;
                }
                $value = $value[$key];
            }

            return $value;
        }

        return null;
    }

    /** @return Service|null */
    public function service(string $id): ?array
    {
        return $this->services()[$id] ?? null;
    }

    public function serviceClass(string $id): ?string
    {
        return $this->resolveService($id)['service']['class'] ?? null;
    }

    /**
     * Include every visited ID, even missing services, so callers can track cache dependencies.
     * @return array{service: Service|null, ids: non-empty-list<string>}
     */
    public function resolveService(string $id): array
    {
        $seen = [];
        $ids = [];
        while (!isset($seen[$id])) {
            $seen[$id] = true;
            $ids[] = $id;
            $service = $this->service($id);
            if ($service === null || $service['abstract'] || $service['factory']) {
                return ['service' => null, 'ids' => $ids];
            }
            if ($service['alias'] === null) {
                return ['service' => $service, 'ids' => $ids];
            }
            $id = $service['alias'];
        }

        return ['service' => null, 'ids' => $ids];
    }

    /** @return list<array<array-key, mixed>> */
    public function listeners(): array
    {
        if ($this->listeners !== null) {
            return $this->listeners;
        }
        $this->load();
        $listeners = [];
        foreach ($this->xml?->services->service ?? [] as $service) {
            if ((string) $service['id'] !== 'debug.event_dispatcher') {
                continue;
            }
            foreach ($service->call as $call) {
                if ((string) $call['method'] !== 'addListener') {
                    continue;
                }

                $listeners[] = self::arguments($call);
            }
        }

        return $this->listeners = $listeners;
    }

    /** @return array<string, array<array-key, mixed>> */
    public function workflows(): array
    {
        $workflows = [];
        foreach ($this->services() as $id => $service) {
            if ($service['class'] !== 'Symfony\\Component\\Workflow\\Definition') {
                continue;
            }

            $workflows[$id] = $service['arguments'];
        }

        return $workflows;
    }

    /** @return array<string, Service> */
    public function workflowServices(): array
    {
        return array_filter($this->services(), static fn (array $service): bool => !$service['abstract'] && in_array($service['class'], [
            'Symfony\\Component\\Workflow\\Workflow',
            'Symfony\\Component\\Workflow\\StateMachine',
        ], true));
    }

    /** @return array<string, Service> */
    private function services(): array
    {
        $this->load();
        if ($this->services !== null) {
            return $this->services;
        }
        $this->services = [];
        foreach ($this->xml?->services->service ?? [] as $service) {
            $this->services[(string) $service['id']] = self::decodeService($service);
        }

        return $this->services;
    }

    /** @return Service */
    private static function decodeService(SimpleXMLElement $service): array
    {
        return [
            'class' => isset($service['class']) ? (string) $service['class'] : null,
            'alias' => isset($service['alias']) ? (string) $service['alias'] : null,
            'abstract' => (string) $service['abstract'] === 'true',
            'factory' => isset($service->factory) || isset($service['factory-class']) || isset($service['factory-service']),
            'arguments' => self::arguments($service),
        ];
    }

    /** @return array<array-key, mixed> */
    private static function arguments(SimpleXMLElement $node): array
    {
        $arguments = [];
        foreach ($node->argument as $argument) {
            if (isset($argument['key'])) {
                $arguments[(string) $argument['key']] = self::decode($argument);
            } else {
                $arguments[] = self::decode($argument);
            }
        }

        return $arguments;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }
        if ($this->path === null) {
            $this->loaded = true;
            return;
        }
        if (isset(self::$validatedXml[$this->path])) {
            $this->xml = self::$validatedXml[$this->path];
            $this->loaded = true;
            return;
        }
        // Suppress filesystem warnings too: parser diagnostics can contain literal secrets.
        $contents = @file_get_contents($this->path);
        $reason = 'file is missing or unreadable';
        if ($contents !== false) {
            $previous = libxml_use_internal_errors(true);
            try {
                $this->xml = simplexml_load_string($contents, options: LIBXML_NONET) ?: null;
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            $reason = 'XML cannot be parsed';
            if ($this->xml !== null) {
                $reason = 'missing "pimcore.config"';
                foreach ($this->xml->parameters->parameter as $parameter) {
                    if ((string) $parameter['key'] === 'pimcore.config' && (string) $parameter['type'] === 'collection') {
                        $this->loaded = true;
                        return;
                    }
                }
            }
        }
        $this->xml = null;
        if ($this->source === 'own') {
            throw new InvalidArgumentException('Cannot read Pimcore Container XML "' . $this->path . '".');
        }
        $this->disabledReason = $reason;
        $this->loaded = true;
    }

    private static function decode(SimpleXMLElement $node): mixed
    {
        $type = (string) $node['type'];
        $text = (string) $node;
        if ($type === 'collection') {
            $values = [];
            foreach ($node->children() as $child) {
                if (isset($child['key'])) {
                    $values[(string) $child['key']] = self::decode($child);
                } else {
                    $values[] = self::decode($child);
                }
            }

            return $values;
        }
        if ($type === 'service' || $type === 'service_closure') {
            return isset($node->service)
                ? self::decodeService($node->service)
                : ['type' => $type, 'id' => (string) $node['id']];
        }
        if ($type === 'binary') {
            return base64_decode($text, true);
        }
        if ($type === 'string') {
            return $text;
        }
        if ($type === 'null' || $text === 'null') {
            return null;
        }
        if ($text === 'true' || $text === 'false') {
            return $text === 'true';
        }
        if (is_numeric($text)) {
            return $text + 0;
        }

        return $text;
    }
}
