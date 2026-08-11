<?php

namespace App\Services\Users;

use App\Models\SetupToken;
use Illuminate\Support\Str;

/**
 * Issues and validates one-time setup links used to bootstrap the first
 * (root) user on an instance with no env-var access of its own — e.g. a
 * future Stripe-provisioned instance. The token itself is the credential,
 * so only its hash is ever persisted (same pattern as password reset
 * tokens): a leaked DB row can't be turned back into a usable link.
 */
class SetupTokenService
{
    protected const TOKEN_LENGTH = 64;

    public const TTL_HOURS = 24;

    /**
     * Invalidate any still-valid tokens and issue a new one. Only one valid
     * token should ever exist at a time. Returns the raw token — it is
     * never persisted and cannot be recovered later, only reissued.
     */
    public function generate(?string $email = null): string
    {
        SetupToken::whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);

        $rawToken = Str::random(self::TOKEN_LENGTH);

        SetupToken::create([
            'token_hash' => $this->hash($rawToken),
            'email'      => $email,
            'expires_at' => now()->addHours(self::TTL_HOURS),
        ]);

        return $rawToken;
    }

    public function validate(string $rawToken): ?SetupToken
    {
        $token = SetupToken::where('token_hash', $this->hash($rawToken))->first();

        if (! $token || $token->used_at !== null || $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }

    public function consume(SetupToken $token): void
    {
        $token->update(['used_at' => now()]);
    }

    protected function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
