<?php

namespace App\Support;

use Illuminate\Http\Request;


 class ApiLocale
{
    protected const SUPPORTED_LOCALES = ['de', 'en'];

    public static function resolve(Request $request): string
    {
        foreach (self::parseAcceptLanguage($request->header('Accept-Language', '')) as $locale) {
            if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
                return $locale;
            }
        }

        return config('app.locale');
    }

    /**
     * "en-US,en;q=0.9,de;q=0.8" -> ['en', 'de']
     * */
    protected static function parseAcceptLanguage(string $header): array
    {
        if ($header === '') {
            return [];
        }

        $languages = [];

        foreach (explode(',', $header) as $part) {
            [$tag, $q] = array_pad(explode(';q=', trim($part)), 2, '1');

            $primary = strtolower(substr(trim($tag), 0, 2));

            if ($primary !== '') {
                $languages[$primary] = max((float) $q, $languages[$primary] ?? 0);
            }
        }

        arsort($languages);

        return array_keys($languages);
    }
}
