<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Settings\Settings;
use App\Models\Settings\SettingValue;
use App\Models\DropdownList;
use App\Support\FormatOptions;
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
      ->orderBy('sort_order')
      ->get();

    $tz = SettingValue::query()
      ->where('key', 'timezone')
      ->value('value')
      ?: config('app.timezone', 'UTC');


    return Inertia::render('Settings/Page', [
      'item' => $settingsItem,
      'values' => $values,
      'dateFormatOptions' => FormatOptions::dateFormatOptions($tz),
      'datetimeFormatOptions' => FormatOptions::datetimeFormatOptions($tz),
      'timezoneOptions' => $this->timezoneOptions($tz),
      'currencyOptions' => DropdownList::get('currency_list')->values ?? [],
      'themeOptions' => config('preferences.theme_options'),
    ]);
  }


  public function notifications()
  {
    $values = SettingValue::where('setting_item', 'notifications')
      ->where('autoload', 1)
      ->orderBy('sort_order')
      ->get();

    return Inertia::render('Settings/Notifications', [
      'item' => Settings::getItem('system', 'notifications'),
      'values' => $values,
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

  private function timezoneOptions(string $currentTz): array
  {
    $options = [];

    foreach (CarbonTimeZone::listIdentifiers() as $tz) {
      $now = now()->setTimezone($tz);

      $offset = $now->format('P');
      $abbr   = $now->format('T');

      $parts = explode('/', $tz);
      $city  = str_replace('_', ' ', end($parts));

      $options[] = [
        'value'       => $tz,
        'label'       => "{$city} (UTC{$offset})",
        'description' => $tz . " • {$abbr}",
        'isActive'    => $tz === $currentTz,
      ];
    }

    usort($options, fn($a, $b) => strcmp($a['label'], $b['label']));

    return $options;
  }

}
