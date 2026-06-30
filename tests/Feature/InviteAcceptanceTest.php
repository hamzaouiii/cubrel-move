<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InviteAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression test: before the User::booted() fix, this crashed with
     * "Unknown column 'owner_id' in 'field list'" — BaseModule::booted()
     * tried to auto-fill owner_id on every model including User, but the
     * users table has no such column. This affected every invite
     * acceptance in production, not just an empty-database edge case.
     */
    public function test_invite_can_be_accepted_and_creates_a_real_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $invite = UserInvite::create([
            'email'      => 'newhire@example.com',
            'token'      => Str::random(64),
            'invited_by' => $admin->id,
            'is_admin'   => false,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post("/invites/{$invite->token}/accept", [
            'first_name'            => 'New',
            'last_name'             => 'Hire',
            'username'              => 'new.hire',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('username', 'new.hire')->first();

        $this->assertNotNull($user);
        $this->assertSame('newhire@example.com', $user->email);
        $this->assertFalse($user->is_admin);
        $response->assertRedirect('/users/'.$user->id);
        $this->assertAuthenticated();
    }
}
