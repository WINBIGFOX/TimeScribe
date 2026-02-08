<?php

declare(strict_types=1);

use App\Enums\TimestampTypeEnum;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\TimestampService;
use Illuminate\Support\Facades\Date;

it('merges timestamps and deletes the previous one', function (): void {
    $beforeStart = Date::parse('2025-01-01 09:00:00');
    $beforeEnd = Date::parse('2025-01-01 10:00:00');
    $currentEnd = Date::parse('2025-01-01 11:00:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
        'description' => 'Previous note',
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeEnd,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->not->toBeNull();
    expect($merged?->started_at->equalTo($beforeStart))->toBeTrue();
    expect($merged?->ended_at->equalTo($currentEnd))->toBeTrue();
    expect($merged?->description)->toBe('Previous note');
    expect(Timestamp::find($timestampBefore->id))->toBeNull();
});

it('combines descriptions when both timestamps have notes', function (): void {
    $beforeStart = Date::parse('2025-01-02 09:00:00');
    $beforeEnd = Date::parse('2025-01-02 10:00:00');
    $currentEnd = Date::parse('2025-01-02 11:00:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
        'description' => 'Before note',
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeEnd,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
        'description' => 'Current note',
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->not->toBeNull();
    expect($merged?->description)->toBe("Before note\nCurrent note");
});

it('does not merge when paid flags differ', function (): void {
    $beforeStart = Date::parse('2025-01-03 09:00:00');
    $beforeEnd = Date::parse('2025-01-03 10:00:00');
    $currentEnd = Date::parse('2025-01-03 11:00:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
        'paid' => true,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeEnd,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
        'paid' => false,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
    expect(Timestamp::count())->toBe(2);
    expect(Timestamp::find($timestampBefore->id))->not->toBeNull();
    expect($timestamp->fresh()->started_at->equalTo($beforeEnd))->toBeTrue();
});

it('merges when timestamps cross a minute boundary within 59 seconds', function (): void {
    $beforeStart = Date::parse('2025-01-04 09:00:00');
    $beforeEnd = Date::parse('2025-01-04 10:00:50');
    $currentStart = Date::parse('2025-01-04 10:01:10');
    $currentEnd = Date::parse('2025-01-04 10:02:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->not->toBeNull();
    expect($merged?->started_at->equalTo($beforeStart))->toBeTrue();
    expect($merged?->ended_at->equalTo($currentEnd))->toBeTrue();
});

it('does not merge when the previous timestamp ends after the current start in the next minute', function (): void {
    $beforeStart = Date::parse('2025-01-05 09:00:00');
    $beforeEnd = Date::parse('2025-01-05 10:01:10');
    $currentStart = Date::parse('2025-01-05 10:00:50');
    $currentEnd = Date::parse('2025-01-05 10:02:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
    expect(Timestamp::find($timestampBefore->id))->not->toBeNull();
    expect($timestamp->fresh()->started_at->equalTo($currentStart))->toBeTrue();
});

it('does not merge when timestamps are more than one minute apart', function (): void {
    $beforeStart = Date::parse('2025-01-06 09:00:00');
    $beforeEnd = Date::parse('2025-01-06 10:00:00');
    $currentStart = Date::parse('2025-01-06 10:01:05');
    $currentEnd = Date::parse('2025-01-06 10:02:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
    expect(Timestamp::find($timestampBefore->id))->not->toBeNull();
});

it('does not merge when types differ', function (): void {
    $beforeStart = Date::parse('2025-01-07 09:00:00');
    $beforeEnd = Date::parse('2025-01-07 10:00:10');
    $currentStart = Date::parse('2025-01-07 10:00:20');
    $currentEnd = Date::parse('2025-01-07 10:01:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::BREAK,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
});

it('does not merge when projects differ', function (): void {
    $beforeStart = Date::parse('2025-01-08 09:00:00');
    $beforeEnd = Date::parse('2025-01-08 10:00:10');
    $currentStart = Date::parse('2025-01-08 10:00:20');
    $currentEnd = Date::parse('2025-01-08 10:01:00');

    $projectBefore = Project::create(['name' => 'Project Before']);
    $projectCurrent = Project::create(['name' => 'Project Current']);

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'project_id' => $projectBefore->id,
        'started_at' => $beforeStart,
        'ended_at' => $beforeEnd,
        'last_ping_at' => $beforeEnd,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'project_id' => $projectCurrent->id,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
});

it('does not merge when the previous timestamp has no end time', function (): void {
    $beforeStart = Date::parse('2025-01-09 09:00:00');
    $currentStart = Date::parse('2025-01-09 10:00:00');
    $currentEnd = Date::parse('2025-01-09 10:01:00');

    $timestampBefore = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $beforeStart,
        'last_ping_at' => $beforeStart,
    ]);

    $timestamp = Timestamp::create([
        'type' => TimestampTypeEnum::WORK,
        'started_at' => $currentStart,
        'ended_at' => $currentEnd,
        'last_ping_at' => $currentEnd,
    ]);

    $merged = TimestampService::merge($timestamp, $timestampBefore);

    expect($merged)->toBeNull();
});
