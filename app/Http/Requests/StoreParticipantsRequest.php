<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'names' => ['required', 'string'],
        ];
    }

    /**
     * The submitted names, split by line, trimmed, and with blank lines removed.
     *
     * @return list<string>
     */
    public function names(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->string('names')))
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->values()
            ->all();
    }
}
