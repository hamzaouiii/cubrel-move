<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Module;

class ModulePolicy
{
  /**
   * Admins bypass all policy checks entirely.
   */
  public function before(User $user, string $ability): bool|null
  {
    return $user->isAdmin() ? true : null;
  }

  /**
   * Can this user see the module and its records at all?
   */
  public function view(User $user, Module $module): bool
  {
    return $module->is_active;
  }

  /**
   * Can this user create records inside this module?
   */
  public function create(User $user, Module $module): bool
  {
    return $module->is_active;
  }

  /**
   * Can this user edit records inside this module?
   */
  public function edit(User $user, Module $module): bool
  {
    return $module->is_active;
  }

  /**
   * Can this user delete records inside this module?
   */
  public function delete(User $user, Module $module): bool
  {
    return $module->is_active;
  }
}
