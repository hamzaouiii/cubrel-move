<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class AdminOnlyModuleScope implements Scope
{
  protected array $adminOnlySlugs = ['users', 'settings'];

  public function apply(Builder $builder, Model $model): void
  {
    if (Auth::check() && Auth::user()->isAdmin()) {
      return; // admins see everything
    }

    $builder->whereNotIn('slug', $this->adminOnlySlugs);
  }
}
