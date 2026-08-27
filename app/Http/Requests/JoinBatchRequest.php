<?php

namespace App\Http\Requests;

use App\Support\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinBatchRequest extends FormRequest
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
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'size:1', 'alpha'],
            'gender' => ['nullable', Rule::in(array_keys(Gender::options()))],
        ];
    }

    public function fullName(): string
    {
        $middle = $this->string('middle_initial')->trim();

        $parts = array_filter([
            $this->string('first_name')->trim()->value(),
            $middle->isNotEmpty() ? $middle->upper()->value().'.' : null,
            $this->string('last_name')->trim()->value(),
        ]);

        return implode(' ', $parts);
    }

    public function genderValue(): string
    {
        return $this->string('gender')->value() ?: Gender::UNSPECIFIED;
    }
}
