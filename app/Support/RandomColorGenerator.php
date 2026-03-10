<?php

namespace App\Support;

class RandomColorGenerator
{
  public static function random(): string
  {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
  }
}
