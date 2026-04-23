<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\UserInvite;

class NoPendingInvite implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $pendingInvite = UserInvite::where('email', $value)
      ->whereNull('accepted_at')
      ->where('expires_at', '>', now())
      ->exists();

    if ($pendingInvite) {
      $fail(__('modules.users.modal.email_pending_invite'));
    }
  }
}
