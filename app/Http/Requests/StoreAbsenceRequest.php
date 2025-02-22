<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AbsenceTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AbsenceTypeEnum::class)],
            'date' => ['required', 'date_format:Y-m-d H:i:s', 'unique:absences,date'],
            'duration' => ['nullable', 'integer', 'min:0.5', 'max:15'],
        ];
    }
}
