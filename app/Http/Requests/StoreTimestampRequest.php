<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimestampRequest extends FormRequest
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
            'type' => ['required', 'in:break,work'],
            'started_at' => ['required', 'date_format:H:i', 'before:ended_at'],
            'ended_at' => [Rule::requiredIf((bool) $this->route('timestamp')?->ended_at ?? false), 'date_format:H:i', 'after:started_at'],
            'description' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'billable' => ['sometimes', 'boolean', Rule::when((bool) $this->route('timestamp')?->paid, ['accepted'])],
        ];
    }

    public function messages(): array
    {
        return [
            'billable.accepted' => __('app.remove the paid mark first.'),
        ];
    }
}
