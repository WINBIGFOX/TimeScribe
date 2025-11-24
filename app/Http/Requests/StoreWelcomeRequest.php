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
            'openAtLogin' => ['required_without_all:workSchedule,vacation', 'boolean'],
            'workSchedule' => ['required_without_all:openAtLogin,vacation', 'array'],
            'workSchedule.sunday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.monday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.tuesday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.wednesday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.thursday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.friday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'workSchedule.saturday' => ['required_with:workSchedule', 'decimal:0,1', 'between:0,15'],
            'vacation' => ['sometimes', 'array'],
            'vacation.default_entitlement_days' => ['required_with:vacation', 'numeric', 'min:0', 'max:365'],
            'vacation.auto_carryover' => ['required_with:vacation', 'boolean'],
            'vacation.minimum_day_hours' => ['sometimes', 'numeric', 'min:0.25', 'max:24'],
        ];
    }
}
