<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateExportSettingsRequest;
use App\Settings\ExportSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Inertia\Inertia;
use Inertia\Response;

class ExportController extends Controller
{
    public function edit(ExportSettings $settings): Response
    {
        return Inertia::render('Settings/Export/Edit', [
            'column_type' => $settings->column_type,
            'column_description' => $settings->column_description,
            'column_project' => $settings->column_project,
            'column_import_source' => $settings->column_import_source,
            'column_start_date' => $settings->column_start_date,
            'column_start_time' => $settings->column_start_time,
            'column_end_date' => $settings->column_end_date,
            'column_end_time' => $settings->column_end_time,
            'column_duration' => $settings->column_duration,
            'column_hourly_rate' => $settings->column_hourly_rate,
            'column_billable_amount' => $settings->column_billable_amount,
            'column_currency' => $settings->column_currency,
            'column_paid' => $settings->column_paid,
            'pdf_paper_size' => $settings->pdf_paper_size,
            'pdf_orientation' => $settings->pdf_orientation,
        ]);
    }

    public function update(UpdateExportSettingsRequest $request, ExportSettings $settings): Redirector|RedirectResponse
    {
        $data = $request->validated();

        $settings->column_type = $data['column_type'];
        $settings->column_description = $data['column_description'];
        $settings->column_project = $data['column_project'];
        $settings->column_import_source = $data['column_import_source'];
        $settings->column_start_date = $data['column_start_date'];
        $settings->column_start_time = $data['column_start_time'];
        $settings->column_end_date = $data['column_end_date'];
        $settings->column_end_time = $data['column_end_time'];
        $settings->column_duration = $data['column_duration'];
        $settings->column_hourly_rate = $data['column_hourly_rate'];
        $settings->column_billable_amount = $data['column_billable_amount'];
        $settings->column_currency = $data['column_currency'];
        $settings->column_paid = $data['column_paid'];
        $settings->pdf_paper_size = $data['pdf_paper_size'];
        $settings->pdf_orientation = $data['pdf_orientation'];
        $settings->save();

        return to_route('settings.export.edit');
    }
}
