<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Settings;
use App\Support\FormatOptions;

class PreferencesController extends Controller
{
  public function index()
  {
    $user = Auth::user();
    $tz = Settings::get('timezone', config('app.timezone', 'UTC'));

    $enabledLanguages = json_decode((string) Settings::get('enabled_languages'), true) ?: ['en'];
    $languageOptions = collect($enabledLanguages)->map(fn ($code) => [
      'value' => $code,
      'label' => strtoupper($code),
    ])->values()->all();

    return Inertia::render('Preferences/Index', [
      'tabs'                  => config('preferences.tabs'),
      'themeOptions'          => config('preferences.theme_options'),
      'systemDefaults'        => self::overridableKeys()
        ->keys()
        ->mapWithKeys(fn ($key) => [$key => Settings::get($key)])
        ->all(),
      'userOverrides'         => $user->preferences ?? [],
      'languageOptions'       => $languageOptions,
      'dateFormatOptions'     => FormatOptions::dateFormatOptions($tz),
      'datetimeFormatOptions' => FormatOptions::datetimeFormatOptions($tz),
    ]);
  }

  public function update(Request $request)
  {
    $overridableKeys = self::overridableKeys();
    $validated = $request->validate($overridableKeys->all());

    $user = Auth::user();
    $preferences = $user->preferences ?? [];

    foreach ($overridableKeys->keys() as $key) {
      if (! array_key_exists($key, $validated) || $validated[$key] === null) {
        unset($preferences[$key]);
        continue;
      }

      $preferences[$key] = $validated[$key];
    }

    $user->update(['preferences' => $preferences]);

    return redirect()->back()->with('success', __('globals.preferences.update_success'));
  }

  private static function overridableKeys()
  {
    return collect(config('preferences.tabs'))
      ->flatMap(fn ($tab) => $tab['fields'])
      ->map(fn ($field) => $field['validation']);
  }
}
