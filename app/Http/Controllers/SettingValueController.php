<?php

namespace App\Http\Controllers;

use App\Models\Settings\SettingItem;
use App\Models\Settings\SettingValue;
use Illuminate\Http\Request;

class SettingValueController extends Controller
{
    // Show all key/values for a given setting page
    public function show(SettingItem $item)
    {
        return response()->json([
            'item' => $item,
            'values' => $item->values()->get(),
        ]);
    }

    public function update(Request $request, SettingItem $item)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);
        foreach ($data['settings'] as $setting) {
            SettingValue::updateOrCreate(
                [
                    'setting_item_id' => $item->id,
                    'key' => $setting['key'],
                ],
                [
                    'value' => $setting['value'],
                ]
            );
        }

        // Clear cache if you use a Settings helper later
        cache()->forget('app_settings');

        return response()->json([
            'message' => 'Settings updated successfully.',
        ]);
    }
}
