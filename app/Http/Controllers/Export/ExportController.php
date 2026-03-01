<?php

declare(strict_types=1);

namespace App\Http\Controllers\Export;

use App\Enums\ExportColumnEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExportRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\Export\ExportService;
use App\Settings\ExportSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Native\Desktop\Dialog;
use Native\Desktop\Facades\Alert;
use Native\Desktop\Facades\Shell;

class ExportController extends Controller
{
    public function create(Request $request)
    {
        $exportType = $request->input('exportType');
        $projects = Project::withTrashed()->orderBy('name')->get();
        $exportSettings = resolve(ExportSettings::class);

        return Inertia::modal('ImportExport/Export/Create', [
            'exportType' => $exportType ?? 'pdf',
            'exportColumns' => ExportColumnEnum::toResource(),
            'projects' => ProjectResource::collection($projects),
            'pdfOrientation' => $exportSettings->pdf_orientation,
            'pdfPaperSize' => $exportSettings->pdf_paper_size,
            'submit_route' => route('export.store'),
        ])->baseRoute('import-export.index');
    }

    public function store(StoreExportRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $exportSettings = resolve(ExportSettings::class);

        foreach ($data['export_columns'] as $column) {
            $exportSettings->{'column_'.$column['key']} = $column['is_visible'];
        }
        $exportSettings->column_order = Arr::pluck($data['export_columns'], 'key');
        if ($data['export_type'] === 'pdf') {
            $exportSettings->pdf_orientation = $data['pdf_orientation'];
            $exportSettings->pdf_paper_size = $data['pdf_paper_size'];
        }
        $exportSettings->save();

        $dateRange = $data['date_range'] ?? [];
        $projectIds = $data['projects'] ?? [];

        $exportService = new ExportService(
            timestampTypes: $data['types'],
            startDate: $dateRange['start'] ?? null,
            endDate: $dateRange['end'] ?? null,
            projectIds: $projectIds
        );

        $extension = [
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
        ];

        $savePath = Dialog::new()->asSheet()
            ->defaultPath($exportService->generateFileName($extension[$data['export_type']]))
            ->button(__('app.save'))
            ->save();

        if ($savePath === null) {
            return back();
        }

        try {
            switch ($data['export_type']) {
                case 'pdf':
                    $exportService->exportAsPdf($savePath);
                    break;
                case 'excel':
                    $exportService->exportAsExcel($savePath);
                    break;
                case 'csv':
                    $exportService->exportAsCsv($savePath);
                    break;
            }
        } catch (\Throwable) {
            Alert::error(
                __('app.export failed'),
                __('app.an error occurred while exporting the data. please try again.')
            );
        }

        Alert::type('info')
            ->title(__('app.export successful'))
            ->show(__('app.the data was successfully exported from timescribe.'));

        Shell::showInFolder($savePath);

        return back();
    }
}
