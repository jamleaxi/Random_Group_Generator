<?php

namespace App\Http\Requests;

use App\Support\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ImportParticipantsRequest extends FormRequest
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
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ];
    }

    /**
     * Parse the uploaded CSV into name/gender entries. Expects a header row
     * with "name" and (optionally) "gender" columns, in any order.
     *
     * @return list<array{name: string, gender: string}>
     */
    public function entries(): array
    {
        $handle = fopen($this->file('csv')->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn (string $column) => strtolower(trim($column)), $header);
        $nameIndex = array_search('name', $header, true);
        $genderIndex = array_search('gender', $header, true);

        $entries = [];

        if ($nameIndex !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $name = Str::title(trim((string) ($row[$nameIndex] ?? '')));

                if ($name === '') {
                    continue;
                }

                $entries[] = [
                    'name' => $name,
                    'gender' => $genderIndex !== false ? Gender::normalize($row[$genderIndex] ?? null) : Gender::UNSPECIFIED,
                ];
            }
        }

        fclose($handle);

        return $entries;
    }
}
