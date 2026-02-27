<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Enums\ExportColumnEnum;
use App\Models\Project;
use App\Models\Timestamp;
use App\Services\LocaleService;
use App\Settings\ExportSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Style\Style;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportService
{
    private readonly ?Carbon $startDate;

    private readonly ?Carbon $endDate;

    public function __construct(private readonly array $timestampTypes, ?string $startDate, ?string $endDate, private readonly array $projectIds = [])
    {
        new LocaleService;
        $this->startDate = $startDate ? Date::parse($startDate)->startOfDay() : null;
        $this->endDate = $endDate ? Date::parse($endDate)->endOfDay() : null;
    }

    public function generateFileName(string $extension): string
    {
        $exportFileName = 'TimeScribe-Export';
        if ($this->startDate && $this->endDate) {
            $exportFileName .= ' — '.$this->startDate->format('Y-m-d').' - '.$this->endDate->format('Y-m-d');
        }
        if ($this->projectIds) {
            $projectNames = Project::withTrashed()->whereIn('id', $this->projectIds)->get('name')->map(fn ($projectName) => Str::slug($projectName))->join(' # ');
            $exportFileName .= ' — '.$projectNames;
        }

        return $exportFileName.'.'.$extension;
    }

    private function getExportData(): Collection
    {
        $timestamps = Timestamp::query()->with(['project']);
        $timestamps->whereIn('type', $this->timestampTypes);
        if ($this->startDate) {
            $timestamps->where('started_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $timestamps->where('ended_at', '<=', $this->endDate);
        }
        if ($this->projectIds) {
            $timestamps->whereIn('project_id', $this->projectIds);
        }

        return $timestamps->latest('started_at')->get();
    }

    public function exportAsCsv(string $filePath): void
    {
        $file = fopen($filePath, 'w');
        fputcsv($file, $this->headerArray(), escape: '\\');

        foreach ($this->getExportData() as $timestamp) {
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

        foreach ($this->getExportData() as $timestamp) {
            $writer->addRow($this->timestampToRowArray($timestamp));
        }
    }

    public function exportAsPdf(string $filePath): void
    {
        $exportData = $this->getExportData();
        $workTime = $exportData->where('type', 'work')->sum('duration');
        $breakTime = $exportData->where('type', 'break')->sum('duration');

        $totalHours = floor($workTime / 3600);
        $totalMinutes = floor(($workTime % 3600) / 60);
        $totalFormatted = sprintf('%d:%02d', $totalHours, $totalMinutes);

        $exportSettings = resolve(ExportSettings::class);

        $projects = [];
        if ($this->projectIds) {
            $projects = Project::withTrashed()->whereIn('id', $this->projectIds)->get(['name', 'color']);
        }

        Pdf::view('pdf.export', [
            'timestamps' => $exportData->map(fn (Timestamp $timestamp): array => $this->timestampToRowArray($timestamp, true))->all(),
            'columns' => $this->headerArray(),
            'startDate' => $this->startDate?->isoFormat('L'),
            'endDate' => $this->endDate?->isoFormat('L'),
            'projects' => $projects,
            'totalHours' => $totalFormatted,
            'breakTime' => $breakTime,
            'workTime' => $workTime,
        ])
            ->format($exportSettings->pdf_paper_size)
            ->orientation($exportSettings->pdf_orientation)
            ->save($filePath);
    }

    private function headerArray(): array
    {
        return collect(ExportColumnEnum::toResource())->filter(fn ($column) => $column['is_visible'])->map(fn ($column) => $column['label'])->toArray();
    }

    private function timestampToRowArray(Timestamp $timestamp, bool $isoFormat = false): array
    {
        $all = [
            'type' => $timestamp['type']->value,
            'description' => $timestamp['description'] ?? '',
            'project' => $timestamp['project'] ? implode(' ', [$timestamp['project']->icon, $timestamp['project']->name]) : '',
            'import_source' => $timestamp['source'] ?? '',
            'duration' => $timestamp['ended_at'] ? gmdate('H:i:s', (int) $timestamp['started_at']->diffInSeconds($timestamp['ended_at'])) : '',
            'hourly_rate' => $timestamp['project']?->hourly_rate ? number_format($timestamp['project']->hourly_rate, 2) : '',
            'billable_amount' => $timestamp['duration'] && $timestamp['project']?->hourly_rate ? number_format($timestamp['duration'] / 60 * $timestamp['project']?->hourly_rate / 60, 2) : '',
            'currency' => $timestamp['project']?->hourly_rate ? $timestamp['project']?->currency ?? '' : '',
            'paid' => $timestamp['paid'] ? 'Yes' : '',
        ];

        if ($isoFormat) {
            $all['start_date'] = $timestamp['started_at']->isoFormat('L');
            $all['start_time'] = $timestamp['started_at']->isoFormat('LTS');
            $all['end_date'] = $timestamp['ended_at'] ? $timestamp['ended_at']->isoFormat('L') : '';
            $all['end_time'] = $timestamp['ended_at'] ? $timestamp['ended_at']->isoFormat('LTS') : '';
        } else {
            $all['start_date'] = $timestamp['started_at']->format('d/m/Y');
            $all['start_time'] = $timestamp['started_at']->format('H:i:s');
            $all['end_date'] = $timestamp['ended_at'] ? $timestamp['ended_at']->format('d/m/Y') : '';
            $all['end_time'] = $timestamp['ended_at'] ? $timestamp['ended_at']->format('H:i:s') : '';
        }

        return collect($this->headerArray())->mapWithKeys(fn ($value, $key): array => [$key => $all[$key] ?? ''])->toArray();
    }
}
