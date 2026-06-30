<?php

namespace Tests\Feature;

use App\Models\SetupToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BootstrapInstanceCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_bails_when_users_already_exist(): void
    {
        User::factory()->create();

        $this->artisan('cubrel:bootstrap')
            ->expectsOutputToContain('already has users')
            ->assertExitCode(1);

        $this->assertSame(0, SetupToken::count());
    }

    public function test_prints_setup_url_when_no_users_exist(): void
    {
        $this->artisan('cubrel:bootstrap')
            ->expectsOutputToContain('/setup/')
            ->assertExitCode(0);

        $this->assertSame(1, SetupToken::count());
    }

    public function test_running_again_regenerates_a_fresh_single_token(): void
    {
        $this->artisan('cubrel:bootstrap')->assertExitCode(0);
        $this->artisan('cubrel:bootstrap')->assertExitCode(0);

        $this->assertSame(1, SetupToken::whereNull('used_at')->count());
    }
}
