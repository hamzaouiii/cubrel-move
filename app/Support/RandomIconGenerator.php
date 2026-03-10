<?php

namespace App\Support;

use App\Models\Icon;

class RandomIconGenerator
{
  public static function random(): ?string
  {
    return Icon::query()
      ->inRandomOrder()
      ->value('class');
  }
}
