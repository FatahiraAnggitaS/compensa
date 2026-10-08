<?php

namespace App\Support;

final class SpreadsheetSafeText
{
    public static function escape(string $value): string
    {
        if (preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
