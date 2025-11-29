<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use App\Models\Settings\Settings;
use App\Models\Settings\SettingItem;
use App\Models\Settings\SettingValue;

class SettingsController extends Controller
{
    public function index(){
    $settings = Settings::with('items')
    ->orderBy('created_at')->get();
      return Inertia::render('Settings/List', [
        'settings'     => $settings]);
    }

  public function show(Request $request)
  {
    $item = SettingItem::where('path', 'like', '%' . $request->path())
        ->with('values')
        ->first();

      return Inertia::render('Settings/Page', [
          'item' => $item,
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

    return back();
  }
}
