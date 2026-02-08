<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Timestamp;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeTimestampRequest extends FormRequest
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
        $timestamp = Timestamp::find($this->input('timestamp'));

        $timestampBeforeRules = ['required', 'exists:timestamps,id'];

        if ($timestamp instanceof Timestamp) {
            $mergeWindowStart = $timestamp->started_at->copy()->subSeconds(59);
            $mergeWindowEnd = $timestamp->started_at->copy()->endOfMinute();

            $timestampBeforeRules = [
                'required',
                Rule::exists('timestamps', 'id')->where(function ($query) use ($timestamp, $mergeWindowStart, $mergeWindowEnd): void {
                    $query->where('type', $timestamp->type->value)
                        ->when(
                            $timestamp->project_id !== null,
                            fn ($query) => $query->where('project_id', $timestamp->project_id),
                            fn ($query) => $query->whereNull('project_id')
                        )
                        ->where('paid', $timestamp->paid)
                        ->whereBetween('ended_at', [$mergeWindowStart, $mergeWindowEnd])
                        ->where('id', '!=', $timestamp->id);
                }),
            ];
        }

        return [
            'timestamp' => ['required', 'exists:timestamps,id'],
            'timestamp_before' => $timestampBeforeRules,
        ];
    }
}
