<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Settings\Settings;
use App\Models\Settings\SettingItem;

class SystemSettingsController extends Controller
{
    public function style(Request $request) {
      $item = SettingItem::where('path', 'like', '%' . $request->path())
      ->with('values')
      ->first();
      return Inertia::render('Settings/System/Style', [
        'item' => $item
      ]);
    }
}
