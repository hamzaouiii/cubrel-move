<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        // /profile sits behind the 'onboarded' middleware, which redirects to
        // /onboarding unless this setting is truthy.
        $this->completeOnboarding();
    }

    public function test_profile_page_is_displayed(): void
    {
        // UserProfileController::index() looks up the 'users' Module
        // registry row via firstOrFail() — without it the route 404s.
        $this->makeModule(['slug' => 'users', 'name' => 'Users', 'path' => '/users']);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        // UserProfileController::update() requires username and uses
        // first_name/last_name (not a single "name" field) — back() needs
        // an explicit referer since the controller doesn't redirect('/profile').
        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/profile', [
                'username'   => 'updated.username',
                'first_name' => 'Test',
                'last_name'  => 'User',
                'email'      => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('updated.username', $user->username);
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }

    // No account-deletion tests: this app has no DELETE /profile route —
    // users are managed by admins (UserController) rather than self-deleted.
}
