<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:break,work',
            'started_at' => 'required|date_format:H:i|before:ended_at',
            'ended_at' => 'required|date_format:H:i|after:started_at',
            'description' => 'nullable|string',
        ];
    }
}
