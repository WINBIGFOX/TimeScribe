<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Timestamp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Style\Style;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportService
{
    private readonly Collection $exportData;

    public function __construct(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $timestamps = Timestamp::query()->with(['project']);
        if ($startDate instanceof Carbon) {
            $timestamps->where('started_at', '>=', $startDate);
        }
        if ($endDate instanceof Carbon) {
            $timestamps->where('ended_at', '<=', $endDate);
        }
        $this->exportData = $timestamps->latest('started_at')->get();
    }

    public function exportAsCsv(string $filePath): void
    {
        $file = fopen($filePath, 'w');
        fputcsv($file, $this->headerArray(), escape: '\\');

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

    private function headerArray(): array
    {
        return [
            'Type',
            'Description',
            'Project',
            'Import Source',
            'Start Date',
            'Start Time',
            'End Date',
            'End Time',
            'Duration (h)',
            'Hourly Rate',
            'Billable Amount',
            'Currency',
            'Paid',
        ];
    }

    private function timestampToRowArray(Timestamp $timestamp): array
    {
        return [
            $timestamp['type']->value,
            $timestamp['description'] ?? '',
            $timestamp['project'] ? implode(' ', [$timestamp['project']->icon, $timestamp['project']->name]) : '',
            $timestamp['source'] ?? '',
            $timestamp['started_at']->format('d/m/Y'),
            $timestamp['started_at']->format('H:i:s'),
            $timestamp['ended_at'] ? $timestamp['ended_at']->format('d/m/Y') : '',
            $timestamp['ended_at'] ? $timestamp['ended_at']->format('H:i:s') : '',
            $timestamp['ended_at'] ? gmdate('H:i:s', (int) $timestamp['started_at']->diffInSeconds($timestamp['ended_at'])) : '',
            $timestamp['project']?->hourly_rate ? number_format($timestamp['project']->hourly_rate, 2) : '',
            $timestamp['duration'] && $timestamp['project']?->hourly_rate ? number_format($timestamp['duration'] / 60 * $timestamp['project']?->hourly_rate / 60, 2) : '',
            $timestamp['project']?->hourly_rate ? $timestamp['project']?->currency ?? '' : '',
            $timestamp['paid'] ? 'Yes' : '',
        ];
    }
}
