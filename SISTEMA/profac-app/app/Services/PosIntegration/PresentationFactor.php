<?php

namespace App\Services\PosIntegration;

class PresentationFactor
{
    public static function valid($value): bool
    {
        if (!preg_match('/^\d+(?:\.0+)?$/D', (string) $value)) {
            return false;
        }
        $integer = ltrim(explode('.', (string) $value, 2)[0], '0');
        return $integer !== '' && (strlen($integer) < 10 || (strlen($integer) === 10 && strcmp($integer, '2147483647') <= 0));
    }
}
