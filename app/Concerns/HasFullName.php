<?php

namespace App\Concerns;

trait HasFullName
{
  public static function bootHasFullName()
  {
    static::saving(function ($model) {
      if (isset($model->first_name) || isset($model->last_name)) {
        if ($model->isDirty('first_name') || $model->isDirty('last_name')) {
          $model->name = trim(($model->first_name ?? '') . ' ' . ($model->last_name ?? ''));
        }
      }
    });
  }
}
