<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ConfirmationModalRule implements ValidationRule
{
    public function __construct(
        private readonly string $title,
        private readonly string $description,
        private readonly string $confirmButtonText = 'Bestätigen',
        private readonly string $cancelButtonText = 'Abbrechen'
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === true) {
            return;
        }
        $fail('confirmationModal', collect([
            'title' => $this->title,
            'description' => $this->description,
            'confirmButtonText' => $this->confirmButtonText,
            'cancelButtonText' => $this->cancelButtonText,
            'confirmRoute' => request()->route()->getName(),
            'confirmParameters' => request()->route()->originalParameters(),
            'confirmMethod' => request()->method(),
            'confirmData' => [...request()->all(), $attribute => true],
        ])->toJson());
    }
}
