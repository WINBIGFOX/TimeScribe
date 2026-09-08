<?php

declare(strict_types=1);

it('provides all strings and preserves replacement placeholders', function (string $locale, string $file): void {
    $english = require __DIR__.'/../../lang/en/'.$file.'.php';
    $translated = require __DIR__.'/../../lang/'.$locale.'/'.$file.'.php';

    expect(array_keys($translated))->toBe(array_keys($english));

    $placeholderMismatches = [];
    foreach ($english as $key => $value) {
        expect($translated[$key])->toBeString()->not->toBeEmpty();
        preg_match_all('/:[a-zA-Z_]+/', (string) $value, $expectedPlaceholders);
        preg_match_all('/:[a-zA-Z_]+/', (string) $translated[$key], $actualPlaceholders);
        sort($expectedPlaceholders[0]);
        sort($actualPlaceholders[0]);
        if ($actualPlaceholders[0] !== $expectedPlaceholders[0]) {
            $placeholderMismatches[$key] = $actualPlaceholders[0];
        }
    }
    expect($placeholderMismatches)->toBe([]);
})->with(['he', 'ar'])->with(['app', 'region', 'validation']);
