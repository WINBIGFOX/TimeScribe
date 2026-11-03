<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimestampPaidRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paid' => ['required', 'boolean', Rule::when(! $this->route('timestamp')->billable, ['declined'])],
        ];
    }

    public function messages(): array
    {
        return [
            'paid.declined' => __('app.non-billable timestamps cannot be marked as paid.'),
        ];
    }
}
