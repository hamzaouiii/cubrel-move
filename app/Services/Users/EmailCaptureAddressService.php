<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Resolves each user's standing email-capture address
 */
class EmailCaptureAddressService
{
    public function addressFor(User $user): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');

        return sprintf('%s@%s', $user->username, $host);
    }

    //Case-insensitive
    public function findUserByRecipientAddress(string $address): ?User
    {
        $localPart = Str::lower(Str::before($address, '@'));

        return User::whereRaw('LOWER(username) = ?', [$localPart])->first();
    }
}
