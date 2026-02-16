<?php

namespace App\Observers;

use App\Models\Settings\SettingValue;
use Illuminate\Support\Facades\Cache;

class SettingValueObserver
{
  public function saved(SettingValue $value)
  {
    Cache::forget('translations.all');
  }

  public function deleted(SettingValue $value)
  {
    Cache::forget('translations.all');
  }
}
