<?php

namespace App\Observers;

use App\Models\Label;
use Illuminate\Support\Facades\Cache;

class LabelObserver
{
  public function saved(Label $label)
  {
    Cache::forget('translations.all');
  }

  public function deleted(Label $label)
  {
    Cache::forget('translations.all');
  }
}
