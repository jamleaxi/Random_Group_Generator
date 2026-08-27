<?php

namespace App\Http\Requests;

use App\Support\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreParticipantsRequest extends FormRequest
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
            'names' => ['nullable', 'string'],
            'entries' => ['nullable', 'array'],
            'entries.*.name' => ['required_with:entries', 'string', 'max:255'],
            'entries.*.gender' => ['nullable', 'string'],
        ];
    }

    /**
     * The submitted name/gender entries, from either the plain-text textarea
     * (one name per line, gender unspecified) or the per-row name+gender
     * fields used when gender balancing is enabled. Names are capitalized.
     *
     * @return list<array{name: string, gender: string}>
     */
    public function entries(): array
    {
        if (is_array($this->input('entries'))) {
            return collect($this->input('entries'))
                ->map(fn (array $entry) => [
                    'name' => Str::title(trim((string) ($entry['name'] ?? ''))),
                    'gender' => Gender::normalize($entry['gender'] ?? null),
                ])
                ->filter(fn (array $entry) => $entry['name'] !== '')
                ->values()
                ->all();
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $this->string('names')))
            ->map(fn (string $name) => Str::title(trim($name)))
            ->filter()
            ->map(fn (string $name) => ['name' => $name, 'gender' => Gender::UNSPECIFIED])
            ->values()
            ->all();
    }
}
