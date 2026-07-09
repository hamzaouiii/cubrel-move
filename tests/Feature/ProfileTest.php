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

        
        
        $this->completeOnboarding();
    }

    public function test_profile_page_is_displayed(): void
    {
        
        
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

    
    
}
