<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkScheduleRequest extends FormRequest
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
            'sunday' => ['required', 'numeric', 'between:0,15'],
            'monday' => ['required', 'numeric', 'between:0,15'],
            'tuesday' => ['required', 'numeric', 'between:0,15'],
            'wednesday' => ['required', 'numeric', 'between:0,15'],
            'thursday' => ['required', 'numeric', 'between:0,15'],
            'friday' => ['required', 'numeric', 'between:0,15'],
            'saturday' => ['required', 'numeric', 'between:0,15'],
            'valid_from' => 'required|date:format:Y-m-d 00:00:00|unique:work_schedules,valid_from,'.$this->route('work_schedule')?->id,
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'valid_from.unique' => __('app.a work plan already begins on the date'),
        ];
    }
}
