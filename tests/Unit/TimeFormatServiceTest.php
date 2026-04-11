<?php

declare(strict_types=1);

use App\Services\TimeFormatService;

it('formats clock durations for display', function (): void {
    expect(TimeFormatService::formatDuration(4500))->toBe('1:15');
});

it('formats decimal-hour durations for display', function (): void {
    expect(TimeFormatService::formatDuration(4500, TimeFormatService::DECIMAL))->toBe('1.25');
});
