<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
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
        LaravelSetList::LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
    ])
    ->withSkip([
        ArrayToFirstClassCallableRector::class,
    ])
    ->withImportNames(importShortClasses: false)
    ->withTypeCoverageLevel(63)
    ->withDeadCodeLevel(59)
    ->withCodeQualityLevel(78);
