<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TimestampTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FillTimestampRequest extends FormRequest
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
            'timestamp_before' => ['required', 'exists:timestamps,id'],
            'timestamp_after' => ['required', 'exists:timestamps,id'],
            'fill_with' => ['required', Rule::enum(TimestampTypeEnum::class)],
        ];
    }
}
