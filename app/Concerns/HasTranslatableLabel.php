<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait HasTranslatableLabel
{
  public function getLabelAttribute($value)
  {
    if (! $value) {
      return $value;
    }

    $translated = __($value);

    return $translated === $value ? $value : $translated;
  }
  public function getSingleLabelAttribute($value)
  {
    if (! $value) {
      return $value;
    }

    $translated = __($value);

    return $translated === $value ? $value : $translated;
  }
}
