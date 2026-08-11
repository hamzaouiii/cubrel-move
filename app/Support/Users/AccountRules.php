<?php

namespace App\Support\Users;

/**
 * Shared validation for "create a user from a self-service form" — used by
 * invite acceptance and setup-link bootstrap so the two flows can't drift.
 */
class AccountRules
{
    public static function newAccount(): array
    {
        return [
            'first_name' => 'nullable|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'username'   => 'required|string|max:255|unique:users',
            'password'   => 'required|confirmed|min:8',
        ];
    }
}
