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
        $timestamps = Timestamp::query();
        if ($startDate instanceof \Carbon\Carbon) {
            $timestamps->where('started_at', '>=', $startDate);
        }
        if ($endDate instanceof \Carbon\Carbon) {
            $timestamps->where('ended_at', '<=', $endDate);
        }
        $this->exportData = $timestamps->orderByDesc('started_at')->get();
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
            'Import Source',
            'Start Date',
            'Start Time',
            'End Date',
            'End Time',
            'Duration (h)',
        ];
    }

    private function timestampToRowArray(Timestamp $timestamp): array
    {
        return [
            $timestamp['type']->value,
            $timestamp['description'] ?? '',
            $timestamp['source'] ?? '',
            $timestamp['started_at']->format('d/m/Y'),
            $timestamp['started_at']->format('H:i:s'),
            $timestamp['ended_at'] ? $timestamp['ended_at']->format('d/m/Y') : '',
            $timestamp['ended_at'] ? $timestamp['ended_at']->format('H:i:s') : '',
            $timestamp['ended_at'] ? gmdate('H:i:s', (int) $timestamp['started_at']->diffInSeconds($timestamp['ended_at'])) : '',
        ];
    }
}
