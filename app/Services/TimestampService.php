<?php

namespace App\Services;

use App\Enums\TimestampTypeEnum;
use App\Models\Timestamp;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimestampService
{
    private static function create(TimestampTypeEnum $type): void
    {
        Timestamp::create([
            'type' => $type,
            'started_at' => now(),
            'last_ping_at' => now(),
        ]);
    }

    private static function makeEndings(): void
    {
        $unclosedDays = Timestamp::whereNull('ended_at')
            ->whereDate('started_at', '<', now()->startOfDay())
            ->get();

        foreach ($unclosedDays as $timestamp) {
            $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
            if ($timestamp->last_ping_at->diffInMinutes(now()) < 60) {
                Timestamp::create([
                    'type' => $timestamp->type,
                    'started_at' => now()->startOfDay(),
                    'last_ping_at' => now(),
                ]);
            }
        }

        Timestamp::whereNull('ended_at')->update(['ended_at' => now()]);
    }

    public static function startWork(): void
    {
        self::makeEndings();
        self::create(TimestampTypeEnum::WORK);
    }

    public static function startBreak(): void
    {
        self::makeEndings();
        self::create(TimestampTypeEnum::BREAK);
    }

    public static function stop(): void
    {
        self::ping();
        self::makeEndings();
    }

    public static function ping(): void
    {

        $activeTimestamps = Timestamp::whereNull('ended_at')
            ->where('last_ping_at', '>=', now()->subHour())->get();

        foreach ($activeTimestamps as $timestamp) {
            $timestamp->update(['last_ping_at' => now()]);

            if ($timestamp->started_at->isYesterday()) {
                $timestamp->update(['ended_at' => now()]);
                Timestamp::create([
                    'type' => $timestamp->type,
                    'started_at' => now()->startOfDay(),
                    'last_ping_at' => now(),
                ]);
            }
        }
        self::createStopByOldTimestamps();
    }

    private static function createStopByOldTimestamps(): void
    {
        $oldTimestamps = Timestamp::whereNull('ended_at')
            ->where('last_ping_at', '<', now()->subHour())->get();

        foreach ($oldTimestamps as $timestamp) {
            $timestamp->update(['ended_at' => $timestamp->last_ping_at]);
        }
    }

    private static function getTime(TimestampTypeEnum $type, ?Carbon $date): int
    {
        if (! $date) {
            $date = Carbon::now();
        }
        $timestamps = Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $date->endOfDay())
            ->where('type', $type)
            ->get();

        return $timestamps->sum(function (Timestamp $timestamp) use ($date) {
            if ($date->isToday()) {
                $fallbackTime = now();
            } else {
                $fallbackTime = $timestamp->last_ping_at;
            }
            $diffTime = $timestamp->ended_at ?? $fallbackTime;

            return $timestamp->started_at->diff($diffTime)->totalSeconds;
        });
    }

    public static function getWorkTime(?Carbon $date = null): int
    {
        return self::getTime(TimestampTypeEnum::WORK, $date);
    }

    public static function getBreakTime(?Carbon $date = null): int
    {
        return self::getTime(TimestampTypeEnum::BREAK, $date);
    }

    public static function getCurrentType(): ?TimestampTypeEnum
    {
        return Timestamp::whereNull('ended_at')->first()?->type;
    }

    public static function getTimestamps(Carbon $date): Collection
    {
        return Timestamp::whereDate('started_at', '>=', $date->startOfDay())
            ->whereDate('started_at', '<=', $date->endOfDay())
            ->orderBy('started_at')
            ->get();
    }
}
