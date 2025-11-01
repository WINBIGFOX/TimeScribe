<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ConfirmationModalRule implements ValidationRule
{
    public function __construct(
        private readonly string $title,
        private readonly string $description,
        private ?string $confirmButtonText = null,
        private ?string $cancelButtonText = null,
    ) {
        $this->confirmButtonText = $confirmButtonText ?? __('app.confirm');
        $this->cancelButtonText = $cancelButtonText ?? __('app.cancel');
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=):PotentiallyTranslatedString  $fail
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
