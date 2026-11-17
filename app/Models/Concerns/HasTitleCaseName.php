<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasTitleCaseName
{
    /**
     * Display and persist names with each word capitalized.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => self::toTitleCaseName($value),
            set: fn (?string $value) => self::toTitleCaseName($value),
        );
    }

    public static function toTitleCaseName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        // Lowercase first so CSV values like "SHEERS" / "white" become consistent,
        // then capitalize each whitespace-separated word.
        return Str::title(mb_strtolower($value));
    }
}
