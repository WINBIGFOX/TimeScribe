<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimestampBillableRequest extends FormRequest
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
            'billable' => ['required', 'boolean', Rule::when($this->route('timestamp')->paid, ['accepted'])],
        ];
    }

    public function messages(): array
    {
        return [
            'billable.accepted' => __('app.remove the paid mark first.'),
        ];
    }
}
