<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExportSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'column_type' => ['required', 'boolean'],
            'column_description' => ['required', 'boolean'],
            'column_project' => ['required', 'boolean'],
            'column_import_source' => ['required', 'boolean'],
            'column_start_date' => ['required', 'boolean'],
            'column_start_time' => ['required', 'boolean'],
            'column_end_date' => ['required', 'boolean'],
            'column_end_time' => ['required', 'boolean'],
            'column_duration' => ['required', 'boolean'],
            'column_hourly_rate' => ['required', 'boolean'],
            'column_billable_amount' => ['required', 'boolean'],
            'column_currency' => ['required', 'boolean'],
            'column_paid' => ['required', 'boolean'],
            'pdf_paper_size' => ['required', 'string', 'in:a4,letter'],
            'pdf_orientation' => ['required', 'string', 'in:portrait,landscape'],
        ];
    }
}
