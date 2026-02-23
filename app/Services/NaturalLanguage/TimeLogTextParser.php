<?php

declare(strict_types=1);

namespace App\Services\NaturalLanguage;

use App\Enums\TimestampTypeEnum;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class TimeLogTextParser
{
    /**
     * @return array{
     *   error: ?string,
     *   type: TimestampTypeEnum,
     *   date: ?Carbon,
     *   start_at: ?Carbon,
     *   end_at: ?Carbon,
     *   duration_seconds: ?int,
     *   guessed_start_at: bool,
     *   description: string,
     *   project_candidate: ?string
     * }
     */
    public function parse(
        string $text,
        Carbon $now,
        ?TimestampTypeEnum $typeOverride = null,
        ?string $dateOverride = null,
        ?string $startOverride = null,
        ?string $endOverride = null,
        ?string $durationOverride = null,
    ): array {
        $description = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        $type = $typeOverride ?? $this->detectType($description);

        $date = $this->detectDate($description, $now, $dateOverride);
        if (! $date instanceof Carbon) {
            return [
                'error' => 'Unable to determine a date. Try adding "yesterday" or use --date=YYYY-MM-DD.',
                'type' => $type,
                'date' => null,
                'start_at' => null,
                'end_at' => null,
                'duration_seconds' => null,
                'guessed_start_at' => false,
                'description' => $description,
                'project_candidate' => null,
            ];
        }

        $startTime = $startOverride ?? $this->detectStartTime($description);
        $endTime = $endOverride ?? $this->detectEndTime($description);
        $durationSeconds = $durationOverride ? $this->parseDurationSeconds($durationOverride) : $this->parseDurationSeconds($description);
        $guessedStartAt = false;

        if (! $startTime && ! $endTime && ! $durationSeconds) {
            return [
                'error' => 'Unable to determine a time range or duration. Try "from 09:00 to 11:00" or "for 2h", or use --start/--end/--duration.',
                'type' => $type,
                'date' => $date,
                'start_at' => null,
                'end_at' => null,
                'duration_seconds' => null,
                'guessed_start_at' => false,
                'description' => $description,
                'project_candidate' => $this->guessProjectCandidate($description),
            ];
        }

        $dateString = $date->format('Y-m-d');

        $startAt = $startTime ? $this->parseTimeOnDate($dateString, $startTime) : null;
        $endAt = $endTime ? $this->parseTimeOnDate($dateString, $endTime) : null;

        if (! $startAt instanceof Carbon && $endAt instanceof Carbon && $durationSeconds) {
            $startAt = $endAt->copy()->subSeconds($durationSeconds);
        }

        if ($startAt instanceof Carbon && ! $endAt instanceof Carbon && $durationSeconds) {
            $endAt = $startAt->copy()->addSeconds($durationSeconds);
        }

        if (! $startAt instanceof Carbon && ! $endAt instanceof Carbon && $durationSeconds) {
            $suggestedStartAt = $date->copy()->setTime(9, 0, 0);
            $startAt = $suggestedStartAt;
            $endAt = $suggestedStartAt->copy()->addSeconds($durationSeconds);
            $guessedStartAt = true;
        }

        if (! $startAt instanceof Carbon || ! $endAt instanceof Carbon) {
            return [
                'error' => 'Unable to compute a start/end time from the provided text/options.',
                'type' => $type,
                'date' => $date,
                'start_at' => null,
                'end_at' => null,
                'duration_seconds' => $durationSeconds,
                'guessed_start_at' => false,
                'description' => $description,
                'project_candidate' => $this->guessProjectCandidate($description),
            ];
        }

        if ($endAt->lessThanOrEqualTo($startAt)) {
            return [
                'error' => 'End time must be after start time.',
                'type' => $type,
                'date' => $date,
                'start_at' => null,
                'end_at' => null,
                'duration_seconds' => $durationSeconds,
                'guessed_start_at' => false,
                'description' => $description,
                'project_candidate' => $this->guessProjectCandidate($description),
            ];
        }

        if (! $startAt->isSameDay($endAt)) {
            return [
                'error' => 'Cross-midnight ranges are not supported. Split into two logs.',
                'type' => $type,
                'date' => $date,
                'start_at' => null,
                'end_at' => null,
                'duration_seconds' => $durationSeconds,
                'guessed_start_at' => false,
                'description' => $description,
                'project_candidate' => $this->guessProjectCandidate($description),
            ];
        }

        return [
            'error' => null,
            'type' => $type,
            'date' => $date,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_seconds' => $durationSeconds,
            'guessed_start_at' => $guessedStartAt,
            'description' => $description,
            'project_candidate' => $this->guessProjectCandidate($description),
        ];
    }

    private function detectType(string $text): TimestampTypeEnum
    {
        $lower = mb_strtolower($text);

        if (preg_match('/\b(break|lunch)\b/u', $lower) === 1) {
            return TimestampTypeEnum::BREAK;
        }

        return TimestampTypeEnum::WORK;
    }

    private function detectDate(string $text, Carbon $now, ?string $override): ?Carbon
    {
        if (filled($override)) {
            try {
                return Date::parse($override)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        $lower = mb_strtolower($text);

        if (str_contains($lower, 'yesterday')) {
            return $now->copy()->subDay()->startOfDay();
        }

        if (str_contains($lower, 'today')) {
            return $now->copy()->startOfDay();
        }

        if (str_contains($lower, 'tomorrow')) {
            return $now->copy()->addDay()->startOfDay();
        }

        if (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/u', $text, $matches) === 1) {
            try {
                return Date::parse($matches[1])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return $now->copy()->startOfDay();
    }

    private function detectStartTime(string $text): ?string
    {
        if (preg_match('/\bfrom\s+([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\b/u', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/\b([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\s*-\s*([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\b/u', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/\bat\s+([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\b/u', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    private function detectEndTime(string $text): ?string
    {
        if (preg_match('/\bto\s+([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\b/u', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/\b([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\s*-\s*([0-9]{1,2}(?::[0-9]{2})?\s*(?:am|pm)?)\b/u', $text, $matches) === 1) {
            return trim($matches[2]);
        }

        return null;
    }

    private function parseTimeOnDate(string $date, string $time): ?Carbon
    {
        try {
            return Date::parse($date.' '.$time);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDurationSeconds(string $text): ?int
    {
        $lower = mb_strtolower($text);

        if (preg_match('/\bfor\s+(\d{1,2}):(\d{2})\b/u', $lower, $matches) === 1) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return ($hours * 3600) + ($minutes * 60);
        }

        $seconds = 0;
        $found = false;

        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(h|hr|hrs|hour|hours)\b/u', $lower, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $found = true;
                $seconds += (int) round(((float) $match[1]) * 3600);
            }
        }

        if (preg_match_all('/\b(\d+)\s*(m|min|mins|minute|minutes)\b/u', $lower, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $found = true;
                $seconds += ((int) $match[1]) * 60;
            }
        }

        if (preg_match_all('/\b(\d+)\s*(s|sec|secs|second|seconds)\b/u', $lower, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as $match) {
                $found = true;
                $seconds += (int) $match[1];
            }
        }

        return $found ? $seconds : null;
    }

    private function guessProjectCandidate(string $text): ?string
    {
        if (preg_match('/\b(?:worked on|working on|on|for)\s+\"([^\"]{2,100})\"/iu', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        if (preg_match('/\b(?:worked on|working on|on|for)\s+([A-Za-z0-9][^,.!?]{1,80}?)(?:\s+for\b|\s+from\b|\s+at\b|$)/iu', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }
}
