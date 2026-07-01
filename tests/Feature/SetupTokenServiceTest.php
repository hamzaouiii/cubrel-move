<?php

namespace Tests\Feature;

use App\Models\SetupToken;
use App\Services\Users\SetupTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupTokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_returns_a_raw_token_and_persists_only_its_hash(): void
    {
        $raw = (new SetupTokenService())->generate();

        $this->assertNotEmpty($raw);
        $this->assertDatabaseMissing('setup_tokens', ['token_hash' => $raw]);
        $this->assertDatabaseHas('setup_tokens', ['token_hash' => hash('sha256', $raw)]);
    }

    public function test_generate_invalidates_previous_valid_tokens(): void
    {
        $service = new SetupTokenService();

        $first = $service->generate();
        $second = $service->generate();

        $this->assertNull($service->validate($first));
        $this->assertNotNull($service->validate($second));
        $this->assertSame(1, SetupToken::whereNull('used_at')->count());
    }

    public function test_validate_returns_null_for_unknown_token(): void
    {
        $service = new SetupTokenService();

        $this->assertNull($service->validate('this-token-does-not-exist'));
    }

    public function test_validate_returns_null_for_expired_token(): void
    {
        $service = new SetupTokenService();
        $raw = $service->generate();

        SetupToken::query()->update(['expires_at' => now()->subMinute()]);

        $this->assertNull($service->validate($raw));
    }

    public function test_validate_returns_null_for_used_token(): void
    {
        $service = new SetupTokenService();
        $raw = $service->generate();

        $token = $service->validate($raw);
        $service->consume($token);

        $this->assertNull($service->validate($raw));
    }

    public function test_validate_returns_token_for_valid_unused_unexpired_token(): void
    {
        $service = new SetupTokenService();
        $raw = $service->generate();

        $token = $service->validate($raw);

        $this->assertInstanceOf(SetupToken::class, $token);
        $this->assertNull($token->used_at);
    }

    public function test_consume_marks_token_used(): void
    {
        $service = new SetupTokenService();
        $raw = $service->generate();
        $token = $service->validate($raw);

        $service->consume($token);

        $this->assertNotNull($token->fresh()->used_at);
    }
}
