<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Project;
use App\Models\Timestamp;
use App\Settings\ProjectSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class ClockifyImportService
{
    private Collection $timestamps;

    private ?string $currency = null;

    public function __construct(private readonly string $csvPath)
    {
        Log::info('ClockifyImportService: Importing CSV file', [
            'csv_path' => $this->csvPath,
        ]);
        if (! $this->verifyFormat()) {
            throw new \Exception('Invalid CSV format');
        }
        $this->checkCurrency();
        $this->timestamps = collect();
    }

    private function verifyFormat(): bool
    {
        $csvFile = fopen($this->csvPath, 'r');
        $header = fgetcsv($csvFile, escape: '\\');
        $firstRow = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        if (count($header) !== 17 || count($firstRow) !== 17) {
            return false;
        }

        $dateRegex = '/\d{2}\/\d{2}\/\d{4}/';
        $timeRegex = '/\d{2}:\d{2}:\d{2}/';

        if (preg_match($dateRegex, (string) $header[9]) || preg_match($dateRegex, (string) $header[11])) {
            return false;
        }

        if (! preg_match($dateRegex, (string) $firstRow[9]) || ! preg_match($dateRegex, (string) $firstRow[11])) {
            return false;
        }

        return preg_match($timeRegex, (string) $firstRow[10]) && preg_match($timeRegex, (string) $firstRow[12]);
    }

    private function checkCurrency(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        $header = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        if (isset($header[15]) && ($header[15] !== '' && $header[15] !== '0')) {
            $currencyString = preg_match('/\(([A-Z]{3})\)/', $header[15], $matches) ? $matches[1] : null;

            if ($currencyString && CurrencyAlpha3::from($currencyString)) {
                $this->currency = strtoupper($currencyString);

                return;
            }
        }

        $this->currency = app(ProjectSettings::class)->defaultCurrency;
    }

    public function import(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        fgetcsv($csvFile, escape: '\\');

        while (($row = fgetcsv($csvFile, escape: '\\')) !== false) {
            $this->readTimestamps($row);
        }

        fclose($csvFile);

        $this->sortTimestamps();
        $this->fixOverlap();
        $this->fixDayOverlap();
        $this->fixDatabaseTimestampCollision();
        $this->addTimestamps();
        $this->createProjects();
        $this->saveTimestamps();

        Log::info('CSV file imported');
    }

    private function readTimestamps(array $row): void
    {
        try {
            $startAt = $this->dateFormat($row[9], $row[10]);
            $endAt = $this->dateFormat($row[11], $row[12]);

            if ($startAt >= now() || $endAt >= now()) {
                return;
            }

            $timestamp = [
                'type' => 'work',
                'started_at' => $startAt->format('Y-m-d H:i:s'),
                'ended_at' => $endAt->format('Y-m-d H:i:s'),
                'source' => 'Clockify',
            ];

            if (! empty($row[0])) {
                $timestamp['project_name'] = $row[0];
                $timestamp['hourly_rate'] = $row[15];
            }
        } catch (\Throwable) {
            return;
        }

        $this->timestamps->push($timestamp);
    }

    private function dateFormat(string $date, string $time): Carbon
    {
        $dateTime = Carbon::createFromFormat('d/m/Y H:i:s', $date.' '.$time);
        if ($dateTime === false) {
            throw new \Exception('Invalid date format');
        }

        return $dateTime;
    }

    private function fixOverlap(): void
    {
        $previousEndDate = null;
        $this->timestamps->transform(function (array $timestamp) use (&$previousEndDate): array {

            if (! $previousEndDate instanceof \Carbon\Carbon) {
                $previousEndDate = Carbon::parse($timestamp['ended_at']);

                return $timestamp;
            }

            $currentStartDate = Carbon::parse($timestamp['started_at']);
            if ($currentStartDate->lessThan($previousEndDate)) {
                $timestamp['started_at'] = $previousEndDate->format('Y-m-d H:i:s');
            }

            $previousEndDate = Carbon::parse($timestamp['ended_at']);

            return $timestamp;
        });
    }

    private function fixDayOverlap(): void
    {
        $addTimestamps = [];
        $this->timestamps->transform(function (array $timestamp) use (&$addTimestamps) {
            $startDate = Carbon::parse($timestamp['started_at']);
            $endDate = Carbon::parse($timestamp['ended_at']);

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
        $this->timestamps->transform(function (array $timestamp) use ($databaseTimestamps, &$addTimestamps, &$timestampsRemoved) {
            $startDate = Carbon::parse($timestamp['started_at']);
            $endDate = Carbon::parse($timestamp['ended_at']);

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
        $this->timestamps = $this->timestamps->map(function (array $timestamp) {
            $timestamp['created_at'] = $timestamp['started_at'];
            $timestamp['updated_at'] = $timestamp['ended_at'];
            $timestamp['last_ping_at'] = $timestamp['ended_at'];

            return $timestamp;
        });
    }

    private function createProjects(): void
    {
        $this->timestamps = $this->timestamps->map(function (array $timestamp) {
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
