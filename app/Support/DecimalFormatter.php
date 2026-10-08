<?php

namespace App\Support;

final class DecimalFormatter
{
    public static function idr(string $value): string
    {
        return 'Rp '.self::decimal($value);
    }

    public static function decimal(string $value): string
    {
        $normalized = bcadd($value, '0', 2);
        $negative = str_starts_with($normalized, '-');
        $unsigned = $negative ? substr($normalized, 1) : $normalized;
        [$integer, $fraction] = explode('.', $unsigned, 2);
        $grouped = preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $integer);

        return ($negative ? '-' : '').$grouped.','.$fraction;
    }
}
