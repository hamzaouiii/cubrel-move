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
    $settings = Settings::with('items')
      ->orderBy('created_at')->get();
    return Inertia::render('Settings/List', [
      'settings'     => $settings
    ]);
  }

  public function show(Request $request)
  {
    $item = SettingItem::where('path', 'like', '%' . $request->path())
      ->with('values')
      ->first();

    return Inertia::render('Settings/Page', [
      'item' => $item,
      'datetimeFormatOptions' => $this->datetimeFormatOptions(),
      'timezoneOptions' => $this->timezoneOptions(),
    ]);
  }

  public function update(Request $request, SettingItem $item)
  {
    $data = $request->validate([
      'values' => 'array',
      'values.*.key' => 'required|string',
      'values.*.value' => 'nullable',
    ]);
    foreach ($data['values'] as $valueData) {
      SettingValue::updateOrCreate(
        [
          'setting_item_id' => $item->id,
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
    $formats = [
      'Y-m-d H:i',
      'd.m.Y H:i',
      'd/m/Y H:i',
      'm/d/Y h:i A',
      'M d, Y H:i',
      'D, d M Y H:i',
    ];

    $example = Carbon::create(2025, 12, 11, 14, 30, 0);

    $tz = SettingValue::query()
      ->where('key', 'timezone')
      ->value('value') ?: config('app.timezone', 'UTC');

    $example->locale(app()->getLocale())->setTimezone($tz);

    $previewMap = [
      'Y-m-d H:i'    => 'YYYY-MM-DD HH:mm',
      'd.m.Y H:i'    => 'DD.MM.YYYY HH:mm',
      'd/m/Y H:i'    => 'DD/MM/YYYY HH:mm',
      'm/d/Y h:i A'  => 'MM/DD/YYYY hh:mm A',
      'M d, Y H:i'   => 'MMM DD, YYYY HH:mm',
      'D, d M Y H:i' => 'ddd, DD MMM YYYY HH:mm',
    ];

    return collect($formats)->map(function ($format) use ($example, $previewMap) {
      $preview = $example->copy()->isoFormat($previewMap[$format] ?? 'YYYY-MM-DD HH:mm');

      return [
        'value' => $format,
        'label' => $preview,
        'description' => "({$format})"
      ];
    })->values()->all();
  }

  private function formatTimezone(string $tz, string $currentTz): array
  {
    $now = now()->setTimezone($tz);
    $offset = $now->format('P'); // +01:00

    return [
      'value' => $tz,                                // stored value
      'label' => "{$tz} (UTC{$offset})",              // display
      'selected' => $tz === $currentTz,               // optional
    ];
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
        'description' => $tz . ($abbr ? " • {$abbr}" : ''),
        'isActive'    => $tz === $currentTz,
      ];
    }

    // OPTIONAL: sort nicely by label
    usort($options, fn($a, $b) => strcmp($a['label'], $b['label']));

    return $options;
  }
}
