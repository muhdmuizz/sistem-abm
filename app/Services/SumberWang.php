<?php

namespace App\Services;

/** Explicit monetary paste boundary. No numeric coercion or financial rounding. */
class SumberWang
{
    public static function normalise(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/D', $value)) {
            return str_replace(',', '', $value);
        }
        return $value;
    }
}
