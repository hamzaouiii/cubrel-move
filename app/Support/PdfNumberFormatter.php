<?php

namespace App\Support;

use function in_array;

class PdfNumberFormatter
{
    public static function symbol(string $code): string
    {
        return match (strtoupper(trim($code))) {
            'EUR'        => '€',
            'USD'        => '$',
            'GBP'        => '£',
            'CHF'        => 'Fr.',
            'JPY', 'CNY' => '¥',
            'CAD'        => 'CA$',
            'AUD'        => 'A$',
            'SEK', 'NOK', 'DKK' => 'kr',
            'PLN'        => 'zł',
            'CZK'        => 'Kč',
            'HUF'        => 'Ft',
            'RON'        => 'lei',
            'BGN'        => 'лв',
            'INR'        => '₹',
            'BRL'        => 'R$',
            'MXN'        => 'MX$',
            'SGD'        => 'S$',
            'HKD'        => 'HK$',
            'TRY'        => '₺',
            'ZAR'        => 'R',
            default      => $code,   // unknown code shown as-is
        };
    }

    public static function format(float $value, int $decimals = 2): string
    {
        $locale = app()->getLocale();

        if (class_exists(\NumberFormatter::class)) {
            $fmt = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $fmt->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
            $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            return $fmt->format($value);
        }

        // Fallback when ext-intl is absent
        $europeLocales = ['de', 'fr', 'nl', 'es', 'it', 'pt', 'pl', 'cs', 'hu', 'ro'];
        $prefix = substr($locale, 0, 2);

        return in_array($prefix, $europeLocales)
            ? number_format($value, $decimals, ',', '.')
            : number_format($value, $decimals, '.', ',');
    }
}
