<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ShortcutRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShortcutSettingsRequest extends FormRequest
{
    private const array SHORTCUT_FIELDS = [
        'startShortcut',
        'stopShortcut',
        'pauseShortcut',
        'overviewShortcut',
    ];

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
            'startShortcut' => ['nullable', 'string', new ShortcutRule(self::SHORTCUT_FIELDS)],
            'stopShortcut' => ['nullable', 'string', new ShortcutRule(self::SHORTCUT_FIELDS)],
            'pauseShortcut' => ['nullable', 'string', new ShortcutRule(self::SHORTCUT_FIELDS)],
            'overviewShortcut' => ['nullable', 'string', new ShortcutRule(self::SHORTCUT_FIELDS)],
        ];
    }
}
