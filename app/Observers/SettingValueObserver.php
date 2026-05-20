<?php

namespace App\Observers;

use App\Models\Settings\SettingValue;
use App\Support\Settings;
use Illuminate\Support\Facades\Cache;

class SettingValueObserver
{
  public function saved(SettingValue $value)
  {
    Settings::clearCache();
    Cache::forget('translations.all');
  }

  public function deleted(SettingValue $value)
  {
    Settings::clearCache();
    Cache::forget('translations.all');
  }
}
