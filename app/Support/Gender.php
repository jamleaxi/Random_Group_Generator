<?php

namespace App\Support;

class Gender
{
    public const MALE = 'male';

    public const FEMALE = 'female';

    public const LGBTQ = 'lgbtq';

    public const UNSPECIFIED = 'unspecified';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::LGBTQ => 'LGBTQ+',
            self::UNSPECIFIED => 'Not Specified',
        ];
    }

    public static function label(?string $gender): string
    {
        return self::options()[$gender] ?? self::options()[self::UNSPECIFIED];
    }

    /**
     * Normalize loosely formatted gender input (e.g. from a CSV cell) into
     * one of the known gender values, defaulting to unspecified.
     */
    public static function normalize(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return match (true) {
            in_array($value, ['male', 'm', 'boy', 'man'], true) => self::MALE,
            in_array($value, ['female', 'f', 'girl', 'woman'], true) => self::FEMALE,
            in_array($value, ['lgbtq', 'lgbtq+', 'lgbt', 'lgbt+'], true) => self::LGBTQ,
            default => self::UNSPECIFIED,
        };
    }

    /**
     * Tailwind text color class for the gender icon.
     */
    public static function colorClass(?string $gender): string
    {
        return match ($gender) {
            self::MALE => 'text-blue-500',
            self::FEMALE => 'text-pink-500',
            self::LGBTQ => 'text-purple-500',
            default => 'text-gray-400',
        };
    }

    /**
     * Inline SVG (or symbol) markup for the gender icon. Kept as tiny inline
     * SVGs so no icon font/package dependency is required.
     */
    public static function icon(?string $gender): string
    {
        return match ($gender) {
            self::MALE => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="10" cy="14" r="6"/><path d="M14.5 9.5 21 3M21 3h-5M21 3v5"/></svg>',
            self::FEMALE => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="9" r="6"/><path d="M12 15v6M9 18h6"/></svg>',
            self::LGBTQ => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M12 21c4-3 7-6.5 7-10.5A5.5 5.5 0 0 0 12 6a5.5 5.5 0 0 0-7 4.5C5 14.5 8 18 12 21Z"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.7 2.2c-.9.5-1.2 1-1.2 1.8"/><path d="M12 17h.01"/></svg>',
        };
    }
}
