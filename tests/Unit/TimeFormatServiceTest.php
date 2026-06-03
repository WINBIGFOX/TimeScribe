<?php

declare(strict_types=1);

use App\Services\TimeFormatService;

it('formats clock durations for display', function (): void {
    expect(TimeFormatService::formatDuration(4500))->toBe('1:15');
});

it('formats decimal-hour durations for display', function (): void {
    expect(TimeFormatService::formatDuration(4500, TimeFormatService::DECIMAL))->toBe('1.25');
});

it('keeps the legacy clock unit rules', function (): void {
    expect(TimeFormatService::unitTranslationKey(4500))->toBe('h')
        ->and(TimeFormatService::unitTranslationKey(1800))->toBe('min');
});

it('uses hours as the unit for decimal display', function (): void {
    expect(TimeFormatService::unitTranslationKey(1800, TimeFormatService::DECIMAL))->toBe('h');
});

it('formats durations with the correct translated unit', function (): void {
    expect(TimeFormatService::formatDurationWithUnit(4500))->toBe('1:15 h')
        ->and(TimeFormatService::formatDurationWithUnit(1800))->toBe('30 min')
        ->and(TimeFormatService::formatDurationWithUnit(4500, TimeFormatService::DECIMAL))->toBe('1.25 h');
});
