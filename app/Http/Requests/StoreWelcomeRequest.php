<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWelcomeRequest extends FormRequest
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
            'openAtLogin' => ['required_without:workSchedule', 'boolean'],
            'workSchedule' => ['required_without:openAtLogin', 'array'],
            'workSchedule.sunday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.monday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.tuesday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.wednesday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.thursday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.friday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.saturday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
        ];
    }
}
