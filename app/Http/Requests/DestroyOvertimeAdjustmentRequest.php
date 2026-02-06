<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ConfirmationModalRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DestroyOvertimeAdjustmentRequest extends FormRequest
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
            'confirm' => [
                new ConfirmationModalRule(
                    title: __('app.are you really sure?'),
                    description: __('app.do you want to remove this entry?'),
                    confirmButtonText: __('app.remove'),
                ),
            ],
        ];
    }
}
