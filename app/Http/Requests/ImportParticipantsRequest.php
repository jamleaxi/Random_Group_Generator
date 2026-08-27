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
        $content = file_get_contents($this->file('csv')->getRealPath());

        if ($content === false) {
            return [];
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $this->normalizeToUtf8($content));
        rewind($handle);

        $header = fgetcsv($handle, null, ',', '"', '\\');

        if ($header === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn (string $column) => strtolower(trim($column)), $header);
        $nameIndex = array_search('name', $header, true);
        $genderIndex = array_search('gender', $header, true);

        $entries = [];

        if ($nameIndex !== false) {
            while (($row = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
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

    /**
     * CSV files saved from Excel on Windows are often Windows-1252 (or
     * plain ISO-8859-1), not UTF-8 — left as-is, accented letters like "Ñ"
     * become invalid byte sequences that render as "?" and throw off
     * title-casing. Detect that and transcode to UTF-8 before parsing.
     */
    private function normalizeToUtf8(string $content): string
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        if ($content === '' || mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        $encoding = mb_detect_encoding($content, ['Windows-1252', 'ISO-8859-1'], true) ?: 'Windows-1252';

        return mb_convert_encoding($content, 'UTF-8', $encoding);
    }
}
