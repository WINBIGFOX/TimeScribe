<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/config',
        __DIR__.'/lang',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withBootstrapFiles([
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/bootstrap/providers.php',
    ])
    ->withPhpSets()
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
    ])
    ->withTypeCoverageLevel(49)
    ->withDeadCodeLevel(49)
    ->withCodeQualityLevel(71)
    ->withImportNames(importShortClasses: false);
