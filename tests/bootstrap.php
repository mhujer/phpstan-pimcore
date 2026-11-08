<?php

declare(strict_types=1);

use Mhujer\PHPStan\Pimcore\Tests\Fixtures;

require __DIR__ . '/../vendor/autoload.php';

// Autoload the committed generated classes matching the installed Pimcore line.
// (PHPStan's test container ignores scanDirectories, so they must be autoloadable.)
$fixtureDir = Fixtures::classesDirectory();

spl_autoload_register(static function (string $class) use ($fixtureDir): void {
    $prefix = 'Pimcore\\Model\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $fixtureDir . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (!is_file($file)) {
        return;
    }

    require $file;
});

// Hand-written project class overrides are shared by both generated fixture lines.
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\Model\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = __DIR__ . '/fixtures/overrides/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (!is_file($file)) {
        return;
    }

    require $file;
});
