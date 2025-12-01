<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Settings\SettingValue;
class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:de,en',
        ]);

        SettingValue::updateOrCreate(
            ['key' => 'app_locale'],
            ['value' => $request->locale, 'autoload' => true]
        );

        Cache::forget('app_settings');

        return back();
    }
}
