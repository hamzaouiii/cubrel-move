<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use App\Models\Settings\Settings;
use App\Models\Settings\SettingItem;
use App\Models\Settings\SettingValue;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class SettingsController extends Controller
{
  public function index()
  {
    $settings = Settings::allActive();
    return Inertia::render('Settings/List', [
      'settings'     => $settings
    ]);
  }

  public function show(Request $request, string $category, string $slug)
  {
    $settingsItem = Settings::getItem($category, $slug);

    $values = SettingValue::where('setting_item',  $slug)
      ->where('autoload', 1)
      ->orderBy('key')
      ->get();
    return Inertia::render('Settings/Page', [
      'item' => $settingsItem,
      'values' => $values,
      'dateFormatOptions' => $this->dateFormatOptions(),
      'datetimeFormatOptions' => $this->datetimeFormatOptions(),
      'timezoneOptions' => $this->timezoneOptions(),
    ]);
  }

  public function update(Request $request, string $item)
  {
    $data = $request->validate([
      'values' => 'array',
      'values.*.key' => 'required|string',
      'values.*.value' => 'nullable',
    ]);
    foreach ($data['values'] as $valueData) {
      SettingValue::updateOrCreate(
        [
          'setting_item' => $item,
          'key' => $valueData['key'],
        ],
        [
          'value' => $valueData['value'],
        ]
      );
    }

    return redirect()->back()->with('success', __('settings.setting_update_success'));
  }

  private function datetimeFormatOptions(): array
  {
    $formatsMap = config('datetime_formats');
    $example = Carbon::create(2025, 12, 11, 14, 30, 0);

    $tz = SettingValue::query()
      ->where('key', 'timezone')
      ->value('value')
      ?: config('app.timezone', 'UTC');

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


  private function timezoneOptions(): array
  {
    $currentTz = SettingValue::query()
      ->where('key', 'timezone')
      ->value('value') ?: config('app.timezone', 'UTC');

    $options = [];

    foreach (CarbonTimeZone::listIdentifiers() as $tz) {
      $now = now()->setTimezone($tz);

      $offset = $now->format('P'); // +01:00
      $abbr   = $now->format('T'); // CET / CEST etc.

      // Human name (last part) e.g. "Berlin" from "Europe/Berlin"
      $parts = explode('/', $tz);
      $city  = str_replace('_', ' ', end($parts));

      $options[] = [
        'value'       => $tz,
        'label'       => "{$city} (UTC{$offset})",
        'description' => $tz . " • {$abbr}",
        'isActive'    => $tz === $currentTz,
      ];
    }

    // OPTIONAL: sort nicely by label
    usort($options, fn($a, $b) => strcmp($a['label'], $b['label']));

    return $options;
  }

  private function dateFormatOptions(): array
  {
    $formatsMap = config('date_formats');
    $example = Carbon::create(2025, 12, 11);

    $tz = SettingValue::query()
      ->where('key', 'timezone')
      ->value('value')
      ?: config('app.timezone', 'UTC');

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
