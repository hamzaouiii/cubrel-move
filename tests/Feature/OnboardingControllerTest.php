<?php

namespace Tests\Feature;

use App\Models\Modules\Account;
use App\Models\Settings\SettingValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsRoot(): User
    {
        $user = User::factory()->create(['is_admin' => true, 'is_root' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_incomplete_onboarding_redirects_other_routes_to_onboarding(): void
    {
        $this->actingAsRoot();

        $response = $this->get('/');

        $response->assertRedirect(route('onboarding.show'));
    }

    public function test_onboarding_show_is_reachable_while_incomplete(): void
    {
        $this->actingAsRoot();

        $response = $this->get('/onboarding');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Onboarding')
            ->where('steps', ['organisation', 'demo-data', 'invite'])
        );
    }

    public function test_onboarding_show_redirects_to_dashboard_once_completed(): void
    {
        $this->actingAsRoot();
        SettingValue::create(['setting_item' => 'system', 'key' => 'onboarding_completed', 'value' => '1']);

        $response = $this->get('/onboarding');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_demo_data_seeds_records_when_populate_true(): void
    {
        $this->actingAsRoot();

        $response = $this->post('/onboarding/demo-data', ['populate' => true]);

        $response->assertRedirect();
        $this->assertGreaterThan(0, Account::count());
    }

    public function test_demo_data_skips_seeding_when_populate_false(): void
    {
        $this->actingAsRoot();

        $response = $this->post('/onboarding/demo-data', ['populate' => false]);

        $response->assertRedirect();
        $this->assertSame(0, Account::count());
    }

    public function test_finish_marks_completed_and_redirects_to_users(): void
    {
        $this->actingAsRoot();

        $response = $this->post('/onboarding/finish', ['destination' => 'users']);

        $response->assertRedirect(route('users.index'));
        $this->assertSame('1', SettingValue::where('key', 'onboarding_completed')->value('value'));
    }

    public function test_finish_defaults_to_dashboard(): void
    {
        $this->actingAsRoot();

        $response = $this->post('/onboarding/finish', []);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_is_reachable_after_finishing(): void
    {
        $this->actingAsRoot();
        $this->post('/onboarding/finish', ['destination' => 'dashboard']);

        $response = $this->get('/');

        $response->assertOk();
    }
}
