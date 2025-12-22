<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Translation\PotentiallyTranslatedString;

class ShortcutRule implements DataAwareRule, ValidationRule
{
    private const array ALLOWED_MODIFIERS = [
        'Cmd',
        'CmdOrCtrl',
        'Ctrl',
        'Alt',
        'Option',
        'AltGr',
        'Shift',
        'Super',
        'Meta',
    ];

    private const array ALLOWED_KEYS = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'F1',
        'F2',
        'F3',
        'F4',
        'F5',
        'F6',
        'F7',
        'F8',
        'F9',
        'F10',
        'F11',
        'F12',
        'F13',
        'F14',
        'F15',
        'F16',
        'F17',
        'F18',
        'F19',
        'F20',
        'F21',
        'F22',
        'F23',
        'F24',
        'Backspace',
        'Delete',
        'Insert',
        'Enter',
        'Up',
        'Down',
        'Left',
        'Right',
        'Home',
        'End',
        'PageUp',
        'PageDown',
        'Esc',
        'VolumeUp',
        'VolumeDown',
        'VolumeMute',
        'MediaNextTrack',
        'MediaPreviousTrack',
        'MediaStop',
        'MediaPlayPause',
        'PrintScreen',
        'Numlock',
        'Scrolllock',
        'Space',
        'Plus',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param  array<int, string>  $shortcutFields
     */
    public function __construct(private readonly array $shortcutFields) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=):PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $parts = explode('+', $value);

        if (count($parts) < 2) {
            $fail(__('app.add at least one modifier key.'));

            return;
        }

        $key = array_pop($parts);

        if (! in_array($key, self::ALLOWED_KEYS, true)) {
            $fail(__('app.this shortcut is not supported.'));

            return;
        }

        $invalidModifier = array_filter($parts, static fn (string $modifier): bool => ! in_array($modifier, self::ALLOWED_MODIFIERS, true));

        if ($invalidModifier !== []) {
            $fail(__('app.this shortcut is not supported.'));

            return;
        }

        $otherShortcuts = Arr::except(
            array_filter(
                Arr::only($this->data, $this->shortcutFields),
                static fn (mixed $shortcut): bool => is_string($shortcut) && $shortcut !== ''
            ),
            [$attribute],
        );

        if (in_array($value, $otherShortcuts, true)) {
            $fail(__('app.this shortcut is already used for another action.'));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
