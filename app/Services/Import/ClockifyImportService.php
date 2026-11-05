<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Project;
use App\Models\Timestamp;
use App\Settings\ProjectSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class ClockifyImportService
{
    private Collection $timestamps;

    private ?string $currency = null;

    /** Normalized header name => column index. */
    private array $columns = [];

    /** Canonical column => known Clockify header names (Clockify localizes exports). */
    private const array HEADER_ALIASES = [
        'project' => ['project', 'projekt'],
        'start_date' => ['start date', 'startdatum'],
        'start_time' => ['start time', 'startzeit'],
        'end_date' => ['end date', 'enddatum'],
        'end_time' => ['end time', 'endzeit'],
        'billable_rate' => ['billable rate', 'abrechenbarer tarif'],
    ];

    private const array REQUIRED_COLUMNS = [
        'start_date' => 'Start Date',
        'start_time' => 'Start Time',
        'end_date' => 'End Date',
        'end_time' => 'End Time',
    ];

    public function __construct(private readonly string $csvPath)
    {
        Log::info('ClockifyImportService: Importing CSV file', [
            'csv_path' => $this->csvPath,
        ]);
        $this->resolveColumns();
        $this->checkCurrency();
        $this->timestamps = collect();
    }

    /**
     * Maps columns by header name instead of position, so extra, missing
     * optional, reordered, or localized columns no longer break the import.
     *
     * @throws \Exception naming the missing columns
     */
    private function resolveColumns(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        $header = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        if ($header === false) {
            throw new \Exception('Clockify CSV file is empty.');
        }

        foreach ($header as $index => $name) {
            $normalized = strtolower(trim(str_replace("\u{FEFF}", '', (string) $name)));
            // Strip suffixes like " (USD)": "Billable Rate (USD)" => "billable rate".
            $normalized = (string) preg_replace('/\s*\(.*\)$/', '', $normalized);
            foreach (self::HEADER_ALIASES as $canonical => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $this->columns[$canonical] ??= $index;
                }
            }
        }

        $missing = [];
        foreach (self::REQUIRED_COLUMNS as $canonical => $display) {
            if (! array_key_exists($canonical, $this->columns)) {
                $missing[] = $display;
            }
        }

        if ($missing !== []) {
            throw new \Exception('Clockify CSV is missing required columns: '.implode(', ', $missing).'.');
        }
    }

    private function checkCurrency(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        $header = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        $rateHeader = is_array($header) && array_key_exists('billable_rate', $this->columns)
            ? (string) $header[$this->columns['billable_rate']]
            : '';

        if ($rateHeader !== '') {
            $currencyString = preg_match('/\(([A-Z]{3})\)/', $rateHeader, $matches) ? $matches[1] : null;

            if ($currencyString && CurrencyAlpha3::from($currencyString)) {
                $this->currency = strtoupper($currencyString);

                return;
            }
        }

        $this->currency = resolve(ProjectSettings::class)->defaultCurrency;
    }

    public function import(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        fgetcsv($csvFile, escape: '\\');

        $rowNumber = 1;
        while (($row = fgetcsv($csvFile, escape: '\\')) !== false) {
            $rowNumber++;
            $this->readTimestamps($row, $rowNumber);
        }

        fclose($csvFile);

        if ($this->timestamps->isEmpty()) {
            throw new \Exception('No time entries found in the Clockify CSV file.');
        }

        $this->sortTimestamps();
        $this->fixOverlap();
        $this->fixDayOverlap();
        $this->fixDatabaseTimestampCollision();
        $this->addTimestamps();
        $this->createProjects();
        $this->saveTimestamps();

        Log::info('CSV file imported');
    }

    private function readTimestamps(array $row, int $rowNumber): void
    {
        $startAt = $this->dateFormat(
            (string) ($row[$this->columns['start_date']] ?? ''),
            (string) ($row[$this->columns['start_time']] ?? ''),
            $rowNumber
        );
        $endAt = $this->dateFormat(
            (string) ($row[$this->columns['end_date']] ?? ''),
            (string) ($row[$this->columns['end_time']] ?? ''),
            $rowNumber
        );

        if ($startAt >= now() || $endAt >= now()) {
            return;
        }

        $timestamp = [
            'type' => 'work',
            'started_at' => $startAt->format('Y-m-d H:i:s'),
            'ended_at' => $endAt->format('Y-m-d H:i:s'),
            'source' => 'Clockify',
        ];

        $project = array_key_exists('project', $this->columns)
            ? trim((string) ($row[$this->columns['project']] ?? ''))
            : '';

        if ($project !== '') {
            $timestamp['project_name'] = $project;
            $rate = array_key_exists('billable_rate', $this->columns)
                ? trim((string) ($row[$this->columns['billable_rate']] ?? ''))
                : '';
            $timestamp['hourly_rate'] = is_numeric($rate) ? $rate : null;
        }

        $this->timestamps->push($timestamp);
    }

    private function dateFormat(string $date, string $time, int $rowNumber): Carbon
    {
        $date = trim($date);
        $time = trim($time);

        foreach (['m/d/Y', 'd/m/Y', 'd.m.Y', 'Y-m-d'] as $dateFormat) {
            foreach (['H:i:s', 'H:i', 'h:i:s A', 'h:i A', 'g:i:s A', 'g:i A'] as $timeFormat) {
                try {
                    $dateTime = Date::createFromFormat($dateFormat.' '.$timeFormat, $date.' '.$time);
                } catch (\Throwable) {
                    continue;
                }
                $errors = Carbon::getLastErrors();
                $clean = $errors === false || (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0);
                if ($dateTime !== false && $clean) {
                    return $dateTime;
                }
            }
        }

        throw new \Exception("Row {$rowNumber}: could not parse date/time '{$date} {$time}'.");
    }

    private function fixOverlap(): void
    {
        $previousEndDate = null;
        $this->timestamps->transform(function (array $timestamp) use (&$previousEndDate): array {

            if (! $previousEndDate instanceof Carbon) {
                $previousEndDate = Date::parse($timestamp['ended_at']);

                return $timestamp;
            }

            $currentStartDate = Date::parse($timestamp['started_at']);
            if ($currentStartDate->lessThan($previousEndDate)) {
                $timestamp['started_at'] = $previousEndDate->format('Y-m-d H:i:s');
            }

            $previousEndDate = Date::parse($timestamp['ended_at']);

            return $timestamp;
        });
    }

    private function fixDayOverlap(): void
    {
        $addTimestamps = [];
        $this->timestamps->transform(function (array $timestamp) use (&$addTimestamps): array {
            $startDate = Date::parse($timestamp['started_at']);
            $endDate = Date::parse($timestamp['ended_at']);

            if ($startDate->isSameDay($endDate)) {
                return $timestamp;
            }
            $copyTimestamp = $timestamp;
            $timestamp['ended_at'] = $startDate->endOfDay()->format('Y-m-d H:i:s');
            $copyTimestamp['started_at'] = $endDate->startOfDay()->format('Y-m-d H:i:s');
            $addTimestamps[] = $copyTimestamp;

            return $timestamp;
        });

        $this->timestamps = $this->timestamps->merge($addTimestamps);

        $this->sortTimestamps();
    }

    private function fixDatabaseTimestampCollision(): void
    {
        $firstDate = $this->timestamps->first();
        $lastDate = $this->timestamps->last();
        $existingDates = [];

        $databaseTimestamps = Timestamp::where('ended_at', '>=', $firstDate['started_at'])
            ->where('started_at', '<=', $lastDate['ended_at'])
            ->get();

        foreach ($databaseTimestamps as $timestamp) {
            $existingDates[] = $timestamp->started_at->format('Y-m-d H:i:s').' - '.$timestamp->ended_at->format('Y-m-d H:i:s');
        }

        $this->timestamps = $this->timestamps->reject(fn ($timestamp): bool => in_array($timestamp['started_at'].' - '.$timestamp['ended_at'], $existingDates))->values();

        $this->resolveCollisionWithDatabaseTimestamps($databaseTimestamps);
    }

    private function resolveCollisionWithDatabaseTimestamps(Collection $databaseTimestamps): void
    {
        $addTimestamps = [];
        $timestampsRemoved = false;
        $this->timestamps->transform(function (array $timestamp) use ($databaseTimestamps, &$addTimestamps, &$timestampsRemoved): ?array {
            $startDate = Date::parse($timestamp['started_at']);
            $endDate = Date::parse($timestamp['ended_at']);

            foreach ($databaseTimestamps as $dbTimestamp) {
                if ($endDate->lessThan($dbTimestamp->started_at) || $startDate->greaterThan($dbTimestamp->ended_at)) {
                    continue;
                }

                if ($startDate->greaterThanOrEqualTo($dbTimestamp->started_at) && $endDate->lessThan($dbTimestamp->ended_at)) {
                    Log::info('ImportService: Timestamp collision detected -> removing timestamp');
                    $timestampsRemoved = true;

                    return null;
                }

                if ($startDate->lessThanOrEqualTo($dbTimestamp->started_at) && $endDate->greaterThanOrEqualTo($dbTimestamp->ended_at)) {
                    Log::info('ImportService: Timestamp collision detected -> splitting timestamp');
                    $copyTimestamp = $timestamp;
                    $timestamp['ended_at'] = $dbTimestamp->started_at->format('Y-m-d H:i:s');
                    $copyTimestamp['started_at'] = $dbTimestamp->ended_at->format('Y-m-d H:i:s');
                    $addTimestamps[] = $copyTimestamp;

                    return $timestamp;
                }

                if ($startDate->lessThan($dbTimestamp->started_at) && $endDate->lessThanOrEqualTo($dbTimestamp->ended_at)) {
                    Log::info('ImportService: Timestamp collision detected -> ended_at modified');
                    $timestamp['ended_at'] = $dbTimestamp->started_at->format('Y-m-d H:i:s');

                    return $timestamp;
                }

                if ($startDate->greaterThan($dbTimestamp->started_at) && $endDate->greaterThanOrEqualTo($dbTimestamp->ended_at)) {
                    Log::info('ImportService: Timestamp collision detected -> started_at modified');
                    $timestamp['started_at'] = $dbTimestamp->ended_at->format('Y-m-d H:i:s');

                    return $timestamp;
                }
            }

            return $timestamp;
        });

        if (count($addTimestamps) > 0) {
            $this->timestamps = $this->timestamps->merge($addTimestamps);
            $this->sortTimestamps();
            $this->resolveCollisionWithDatabaseTimestamps($databaseTimestamps);
        }

        if ($timestampsRemoved) {
            $this->sortTimestamps();
        }
    }

    private function sortTimestamps(): void
    {
        $this->timestamps = $this->timestamps->sortBy('started_at')->unique()->filter()->values();
    }

    private function addTimestamps(): void
    {
        $this->timestamps = $this->timestamps->map(function (array $timestamp): array {
            $timestamp['created_at'] = $timestamp['started_at'];
            $timestamp['updated_at'] = $timestamp['ended_at'];
            $timestamp['last_ping_at'] = $timestamp['ended_at'];

            return $timestamp;
        });
    }

    private function createProjects(): void
    {
        $this->timestamps = $this->timestamps->map(function (array $timestamp): array {
            if (empty($timestamp['project_name'])) {
                return $timestamp;
            }

            $project = Project::firstOrCreate(
                ['name' => $timestamp['project_name']],
                [
                    'description' => __('app.project created from clockify import'),
                    'color' => '#000000',
                    'hourly_rate' => $timestamp['hourly_rate'],
                    'currency' => $this->currency,
                ]
            );

            unset($timestamp['project_name']);
            unset($timestamp['hourly_rate']);

            $timestamp['project_id'] = $project->id;

            return $timestamp;
        });
    }

    private function saveTimestamps(): void
    {
        foreach ($this->timestamps as $timestamp) {
            Timestamp::create($timestamp);
        }
    }
}
