<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Users\InviteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    

    public function test_invite_can_be_accepted_and_creates_a_real_user(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $invite = (new InviteService())->create('newhire@example.com', $admin->id);

        $response = $this->post("/invites/{$invite->plainToken}/accept", [
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
