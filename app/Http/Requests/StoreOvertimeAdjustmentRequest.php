<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\OvertimeAdjustmentTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOvertimeAdjustmentRequest extends FormRequest
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
        $overtimeAdjustment = $this->route('overtimeAdjustment');

        return [
            'effective_date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('overtime_adjustments', 'effective_date')->ignore($overtimeAdjustment),
            ],
            'type' => ['required', Rule::enum(OvertimeAdjustmentTypeEnum::class)],
            'seconds' => ['required', 'integer',  Rule::when(
                $this->input('type') === OvertimeAdjustmentTypeEnum::Relative->value,
                ['not_in:0']
            )],
            'note' => ['nullable', 'string'],
        ];
    }

    #[\Override]
    public function attributes(): array
    {
        return [
            'effective_date' => __('app.effective date'),
            'seconds' => __('app.hour adjustment'),
        ];
    }
}
