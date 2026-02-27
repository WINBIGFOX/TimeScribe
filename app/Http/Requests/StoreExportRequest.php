<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ExportColumnEnum;
use App\Enums\TimestampTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;

class StoreExportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'export_type' => ['required', 'in:csv,excel,pdf'],
            'export_columns' => ['required', 'array'],
            'export_columns.*.key' => ['required', Rule::enum(ExportColumnEnum::class)],
            'export_columns.*.is_visible' => ['required', 'boolean'],
            'date_range' => ['nullable', 'array'],
            'date_range.start' => ['required_with:date_range', 'date_format:Y-m-d'],
            'date_range.end' => ['required_with:date_range', 'date_format:Y-m-d'],
            'pdf_paper_size' => ['required_if:export_type,pdf', Rule::enum(Format::class)],
            'pdf_orientation' => ['required_if:export_type,pdf', Rule::enum(Orientation::class)],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['required_with:projects', 'exists:projects,id'],
            'types' => ['required', 'array'],
            'types.*' => ['required', Rule::enum(TimestampTypeEnum::class)],
        ];
    }
}
