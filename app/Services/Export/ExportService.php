<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Project;
use App\Models\Timestamp;
use App\Settings\ExportSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Style\Style;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportService
{
    private readonly Collection $exportData;

    private readonly ?Carbon $startDate;

    private readonly ?Carbon $endDate;

    private readonly ?string $projectName;

    private readonly string $paperSize;

    private readonly string $orientation;

    /** @var array<string, bool> */
    private readonly array $columns;

    public function __construct(?Carbon $startDate = null, ?Carbon $endDate = null, ?int $projectId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->projectName = $projectId ? Project::find($projectId)?->name : null;

        $settings = resolve(ExportSettings::class);
        $this->paperSize = $settings->pdf_paper_size;
        $this->orientation = $settings->pdf_orientation;
        $this->columns = [
            'type' => $settings->column_type,
            'description' => $settings->column_description,
            'project' => $settings->column_project,
            'import_source' => $settings->column_import_source,
            'start_date' => $settings->column_start_date,
            'start_time' => $settings->column_start_time,
            'end_date' => $settings->column_end_date,
            'end_time' => $settings->column_end_time,
            'duration' => $settings->column_duration,
            'hourly_rate' => $settings->column_hourly_rate,
            'billable_amount' => $settings->column_billable_amount,
            'currency' => $settings->column_currency,
            'paid' => $settings->column_paid,
        ];

        $timestamps = Timestamp::query()->with(['project']);
        if ($startDate instanceof Carbon) {
            $timestamps->where('started_at', '>=', $startDate);
        }
        if ($endDate instanceof Carbon) {
            $timestamps->where('ended_at', '<=', $endDate);
        }
        if ($projectId !== null) {
            $timestamps->where('project_id', $projectId);
        }
        $this->exportData = $timestamps->latest('started_at')->get();
    }

    public function exportAsCsv(string $filePath): void
    {
        $file = fopen($filePath, 'w');
        fputcsv($file, $this->headerArray(), escape:  '\\');

        foreach ($this->exportData as $timestamp) {
            fputcsv($file, $this->timestampToRowArray($timestamp), escape: '\\');
        }

        fclose($file);
    }

    public function exportAsExcel(string $filePath): void
    {
        $style = (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setFontColor('0F172A')
            ->setBackgroundColor('00C9DB');

        $writer = SimpleExcelWriter::create($filePath);
        $writer->setHeaderStyle($style);
        $writer->addHeader($this->headerArray());

        foreach ($this->exportData as $timestamp) {
            $writer->addRow($this->timestampToRowArray($timestamp));
        }
    }

    public function exportAsPdf(string $filePath): void
    {
        $totalSeconds = $this->exportData->reduce(function (int $carry, Timestamp $ts): int {
            if ($ts->ended_at !== null) {
                return $carry + (int) $ts->started_at->diffInSeconds($ts->ended_at);
            }

            return $carry;
        }, 0);

        $totalHours = floor($totalSeconds / 3600);
        $totalMinutes = floor(($totalSeconds % 3600) / 60);
        $totalFormatted = sprintf('%d:%02d', $totalHours, $totalMinutes);

        Pdf::view('pdf.export', [
            'timestamps' => $this->exportData,
            'columns' => $this->columns,
            'startDate' => $this->startDate?->toDateString(),
            'endDate' => $this->endDate?->toDateString(),
            'projectName' => $this->projectName,
            'totalHours' => $totalFormatted,
        ])
            ->format($this->paperSize)
            ->orientation($this->orientation)
            ->save($filePath);
    }

    private function headerArray(): array
    {
        $all = [
            'type' => 'Type',
            'description' => 'Description',
            'project' => 'Project',
            'import_source' => 'Import Source',
            'start_date' => 'Start Date',
            'start_time' => 'Start Time',
            'end_date' => 'End Date',
            'end_time' => 'End Time',
            'duration' => 'Duration (h)',
            'hourly_rate' => 'Hourly Rate',
            'billable_amount' => 'Billable Amount',
            'currency' => 'Currency',
            'paid' => 'Paid',
        ];

        return array_values(
            array_filter($all, fn (string $key) => $this->columns[$key] ?? true, ARRAY_FILTER_USE_KEY)
        );
    }

    private function timestampToRowArray(Timestamp $timestamp): array
    {
        $all = [
            'type' => $timestamp['type']->value,
            'description' => $timestamp['description'] ?? '',
            'project' => $timestamp['project'] ? implode(' ', [$timestamp['project']->icon, $timestamp['project']->name]) : '',
            'import_source' => $timestamp['source'] ?? '',
            'start_date' => $timestamp['started_at']->format('d/m/Y'),
            'start_time' => $timestamp['started_at']->format('H:i:s'),
            'end_date' => $timestamp['ended_at'] ? $timestamp['ended_at']->format('d/m/Y') : '',
            'end_time' => $timestamp['ended_at'] ? $timestamp['ended_at']->format('H:i:s') : '',
            'duration' => $timestamp['ended_at'] ? gmdate('H:i:s', (int) $timestamp['started_at']->diffInSeconds($timestamp['ended_at'])) : '',
            'hourly_rate' => $timestamp['project']?->hourly_rate ? number_format($timestamp['project']->hourly_rate, 2) : '',
            'billable_amount' => $timestamp['duration'] && $timestamp['project']?->hourly_rate ? number_format($timestamp['duration'] / 60 * $timestamp['project']?->hourly_rate / 60, 2) : '',
            'currency' => $timestamp['project']?->hourly_rate ? $timestamp['project']?->currency ?? '' : '',
            'paid' => $timestamp['paid'] ? 'Yes' : '',
        ];

        return array_values(
            array_filter($all, fn (string $key) => $this->columns[$key] ?? true, ARRAY_FILTER_USE_KEY)
        );
    }
}
