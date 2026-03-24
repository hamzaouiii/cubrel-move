<?php

namespace App\Observers;

use App\Models\Settings\SettingValue;
use App\Services\Settings\SettingService;
use Illuminate\Support\Facades\Cache;

class SettingValueObserver
{
  public function saved(SettingValue $value)
  {
    SettingService::clearCache();
    Cache::forget('translations.all');
  }

  public function deleted(SettingValue $value)
  {
    SettingService::clearCache();
    Cache::forget('translations.all');
  }
}
