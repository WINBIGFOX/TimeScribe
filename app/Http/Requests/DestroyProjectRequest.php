<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ConfirmationModalRule;
use Illuminate\Foundation\Http\FormRequest;

class DestroyProjectRequest extends FormRequest
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
            'confirm' => [
                new ConfirmationModalRule(
                    title: __('app.are you really sure?'),
                    description: __('app.do you want to archive this project?'),
                    confirmButtonText: __('app.archive'),
                ),
            ],
        ];
    }
}
