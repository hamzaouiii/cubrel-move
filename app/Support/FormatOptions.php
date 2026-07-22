<?php

namespace App\Support;

use Carbon\Carbon;

class FormatOptions
{
  public static function dateFormatOptions(string $tz): array
  {
    return self::buildOptions(config('date_formats'), Carbon::create(2025, 12, 11), $tz);
  }

  public static function datetimeFormatOptions(string $tz): array
  {
    return self::buildOptions(config('datetime_formats'), Carbon::create(2025, 12, 11, 14, 30, 0), $tz);
  }

  private static function buildOptions(array $formatsMap, Carbon $example, string $tz): array
  {
    $example->locale(app()->getLocale())->setTimezone($tz);

    return collect($formatsMap)->map(function ($previewFormat, $phpFormat) use ($example) {
      $preview = $example->copy()->isoFormat($previewFormat);

      return [
        'value'       => $phpFormat,
        'label'       => $preview,
        'description' => "({$phpFormat})",
      ];
    })->values()->all();
  }
}
