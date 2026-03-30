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

    /**
     * @var array<string, int|null>
     */
    private array $columnIndexes = [];

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
        if ($csvFile === false) {
            return false;
        }

        $header = fgetcsv($csvFile, escape: '\\');
        $firstRow = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        if (! is_array($header) || ! is_array($firstRow)) {
            return false;
        }

        $columnIndexes = $this->resolveColumnIndexes($header);
        if ($columnIndexes === null) {
            return false;
        }

        $this->columnIndexes = $columnIndexes;

        try {
            dd($this->getRowValue($firstRow, 'start_date'),
                $this->getRowValue($firstRow, 'start_time'),
                $this->getRowValue($firstRow, 'end_date'),
                $this->getRowValue($firstRow, 'end_time')
            );

            $this->dateFormat(
                $this->getRowValue($firstRow, 'start_date'),
                $this->getRowValue($firstRow, 'start_time'),
            );
            $this->dateFormat(
                $this->getRowValue($firstRow, 'end_date'),
                $this->getRowValue($firstRow, 'end_time'),
            );
        } catch (\Throwable) {
            dd('hier');

            return false;
        }

        return true;
    }

    private function checkCurrency(): void
    {
        $csvFile = fopen($this->csvPath, 'r');
        $header = fgetcsv($csvFile, escape: '\\');
        fclose($csvFile);

        if (is_array($header)) {
            foreach ($header as $columnHeader) {
                $currencyString = preg_match('/\(([A-Z]{3})\)/', (string) $columnHeader, $matches) ? $matches[1] : null;

                if ($currencyString !== null && CurrencyAlpha3::tryFrom($currencyString) instanceof CurrencyAlpha3) {
                    $this->currency = strtoupper($currencyString);

                    return;
                }
            }
        }

        $this->currency = resolve(ProjectSettings::class)->defaultCurrency;
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
            $startAt = $this->dateFormat(
                $this->getRowValue($row, 'start_date'),
                $this->getRowValue($row, 'start_time'),
            );
            $endAt = $this->dateFormat(
                $this->getRowValue($row, 'end_date'),
                $this->getRowValue($row, 'end_time'),
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

            $projectName = $this->getRowValue($row, 'project_name');
            if ($projectName !== '') {
                $timestamp['project_name'] = $projectName;

                $hourlyRate = $this->parseHourlyRate($this->getRowValue($row, 'hourly_rate'));
                if ($hourlyRate !== null) {
                    $timestamp['hourly_rate'] = $hourlyRate;
                }
            }
        } catch (\Throwable) {
            return;
        }

        $this->timestamps->push($timestamp);
    }

    /**
     * @param  array<int, string|null>  $header
     * @return array<string, int|null>|null
     */
    private function resolveColumnIndexes(array $header): ?array
    {
        $normalizedHeader = array_map(
            fn (?string $column): string => $this->normalizeHeader((string) $column),
            $header,
        );

        $columnIndexes = [
            'project_name' => $this->findHeaderIndex($normalizedHeader, [
                'project',
                'projekt',
                'proyecto',
                'projet',
                'projeto',
            ]),
            'start_date' => $this->findHeaderIndex($normalizedHeader, [
                'start date',
                'startdatum',
                'fecha de inicio',
                'date de début',
                'data de início',
            ]),
            'start_time' => $this->findHeaderIndex($normalizedHeader, [
                'start time',
                'startzeit',
                'hora de inicio',
                'heure de début',
                'hora de início',
            ]),
            'end_date' => $this->findHeaderIndex($normalizedHeader, [
                'end date',
                'enddatum',
                'fecha de finalización',
                'date de fin',
                'data final',
            ]),
            'end_time' => $this->findHeaderIndex($normalizedHeader, [
                'end time',
                'endzeit',
                'hora de finalización',
                'heure de fin',
                'hora de término',
            ]),
            'hourly_rate' => $this->findHeaderIndexByPrefix($normalizedHeader, [
                'billable rate',
                'abrechenbarer tarif',
                'tarifa facturable',
                'taux facturable',
                'valor faturável',
            ]),
        ];

        foreach (['start_date', 'start_time', 'end_date', 'end_time'] as $requiredColumn) {
            if ($columnIndexes[$requiredColumn] === null) {
                return null;
            }
        }

        return $columnIndexes;
    }

    private function normalizeHeader(string $header): string
    {
        $header = str_replace("\xEF\xBB\xBF", '', $header);
        $header = preg_replace('/\s+/', ' ', trim($header));

        return mb_strtolower($header ?? '');
    }

    /**
     * @param  array<int, string>  $normalizedHeader
     * @param  array<int, string>  $aliases
     */
    private function findHeaderIndex(array $normalizedHeader, array $aliases): ?int
    {
        foreach ($normalizedHeader as $index => $column) {
            if (in_array($column, $aliases, true)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $normalizedHeader
     * @param  array<int, string>  $prefixes
     */
    private function findHeaderIndexByPrefix(array $normalizedHeader, array $prefixes): ?int
    {
        foreach ($normalizedHeader as $index => $column) {
            foreach ($prefixes as $prefix) {
                if ($column === $prefix || str_starts_with($column, $prefix.' ')) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function getRowValue(array $row, string $column): string
    {
        $index = $this->columnIndexes[$column] ?? null;
        if ($index === null) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    private function parseHourlyRate(string $hourlyRate): ?float
    {
        if ($hourlyRate === '') {
            return null;
        }

        $normalizedHourlyRate = str_replace(',', '.', $hourlyRate);
        if (! is_numeric($normalizedHourlyRate)) {
            return null;
        }

        return (float) $normalizedHourlyRate;
    }

    private function dateFormat(string $date, string $time): Carbon
    {
        $dateTime = Date::createFromFormat('d/m/Y H:i:s', $date.' '.$time);
        if ($dateTime === false) {
            throw new \Exception('Invalid date format');
        }

        return $dateTime;
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
        if ($this->timestamps->isEmpty()) {
            return;
        }

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
                    'hourly_rate' => $timestamp['hourly_rate'] ?? null,
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
