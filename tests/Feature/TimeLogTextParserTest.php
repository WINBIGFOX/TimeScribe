<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Services\NaturalLanguage\TimeLogTextParser;
use Illuminate\Support\Facades\Date;

it('parses a duration-only log for yesterday', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    $parser = new TimeLogTextParser;
    $result = $parser->parse('yesterday worked on Acme for 2h', now());

    expect($result['error'])->toBeNull();
    expect($result['type'])->toBe(TimestampTypeEnum::WORK);
    expect($result['date']?->toDateString())->toBe('2026-02-21');
    expect($result['duration_seconds'])->toBe(7200);
    expect($result['guessed_start_at'])->toBeTrue();
});

it('parses an explicit time range', function (): void {
    Date::setTestNow(Date::parse('2026-02-22 12:00:00'));

    $parser = new TimeLogTextParser;
    $result = $parser->parse('2026-02-20 break from 09:00 to 09:30', now());

    expect($result['error'])->toBeNull();
    expect($result['type'])->toBe(TimestampTypeEnum::BREAK);
    expect($result['date']?->toDateString())->toBe('2026-02-20');
    expect($result['start_at']?->format('Y-m-d H:i'))->toBe('2026-02-20 09:00');
    expect($result['end_at']?->format('Y-m-d H:i'))->toBe('2026-02-20 09:30');
    expect($result['guessed_start_at'])->toBeFalse();
});
