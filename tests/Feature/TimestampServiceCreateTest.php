<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Date;
use App\Enums\TimestampTypeEnum;
use App\Models\Timestamp;
use App\Services\TimestampService;
use Carbon\Carbon;

it('deletes timestamps fully inside the new range', function (): void {
    $start = Date::parse('2025-01-01 10:00:00');
    $end = Date::parse('2025-01-01 12:00:00');

    $inside = Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => Date::parse('2025-01-01 10:30:00'),
        'ended_at' => Date::parse('2025-01-01 11:00:00'),
        'last_ping_at' => Date::parse('2025-01-01 11:00:00'),
    ]);

    TimestampService::create($start, $end, TimestampTypeEnum::BREAK);

    expect(Timestamp::find($inside->id))->toBeNull();
});

it('trims an existing timestamp that overlaps the new start', function (): void {
    $start = Date::parse('2025-01-01 10:00:00');
    $end = Date::parse('2025-01-01 12:00:00');

    $existing = Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => Date::parse('2025-01-01 09:00:00'),
        'ended_at' => Date::parse('2025-01-01 10:30:00'),
        'last_ping_at' => Date::parse('2025-01-01 10:30:00'),
    ]);

    TimestampService::create($start, $end, TimestampTypeEnum::BREAK);

    $existing = $existing->fresh();

    expect($existing->ended_at->equalTo($start))->toBeTrue();
    expect($existing->last_ping_at->equalTo($start))->toBeTrue();
});

it('trims an existing timestamp that overlaps the new end', function (): void {
    $start = Date::parse('2025-01-01 10:00:00');
    $end = Date::parse('2025-01-01 12:00:00');

    $existing = Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => Date::parse('2025-01-01 11:00:00'),
        'ended_at' => Date::parse('2025-01-01 13:00:00'),
        'last_ping_at' => Date::parse('2025-01-01 13:00:00'),
    ]);

    TimestampService::create($start, $end, TimestampTypeEnum::BREAK);

    $existing = $existing->fresh();

    expect($existing->started_at->equalTo($end))->toBeTrue();
});

it('splits an existing timestamp when the new range sits inside it', function (): void {
    $start = Date::parse('2025-01-01 10:00:00');
    $end = Date::parse('2025-01-01 12:00:00');

    Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => Date::parse('2025-01-01 09:00:00'),
        'ended_at' => Date::parse('2025-01-01 13:00:00'),
        'last_ping_at' => Date::parse('2025-01-01 13:00:00'),
    ]);

    TimestampService::create($start, $end, TimestampTypeEnum::BREAK);

    $timestamps = Timestamp::orderBy('started_at')->get();

    expect($timestamps)->toHaveCount(3);
    expect($timestamps->get(0)->started_at->equalTo(Date::parse('2025-01-01 09:00:00')))->toBeTrue();
    expect($timestamps->get(0)->ended_at->equalTo($start))->toBeTrue();
    expect($timestamps->get(1)->started_at->equalTo($start))->toBeTrue();
    expect($timestamps->get(1)->ended_at->equalTo($end))->toBeTrue();
    expect($timestamps->get(2)->started_at->equalTo($end))->toBeTrue();
    expect($timestamps->get(2)->ended_at->equalTo(Date::parse('2025-01-01 13:00:00')))->toBeTrue();
});
