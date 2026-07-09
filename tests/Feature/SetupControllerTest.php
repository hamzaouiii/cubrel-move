<?php

namespace Tests\Feature;

use App\Models\Settings\SettingValue;
use App\Models\User;
use App\Services\Users\SetupTokenService;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SetupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_redirects_to_login_when_users_already_exist(): void
    {
        User::factory()->create();
        $token = (new SetupTokenService())->generate();

        $response = $this->get("/setup/{$token}");

        $response->assertRedirect(route('login'));
    }

    public function test_show_renders_invalid_for_bad_token(): void
    {
        $response = $this->get('/setup/not-a-real-token');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Setup')
            ->where('invalid', true)
        );
    }

    public function test_show_renders_form_for_valid_token(): void
    {
        $token = (new SetupTokenService())->generate();

        $response = $this->get("/setup/{$token}");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Setup')
            ->where('invalid', false)
            ->where('token', $token)
        );
    }

    public function test_show_passes_valid_locale_query_param_and_sets_app_locale(): void
    {
        $token = (new SetupTokenService())->generate();

        $response = $this->get("/setup/{$token}?locale=de");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Setup')
            ->where('locale', 'de')
        );
        $this->assertSame('de', app()->getLocale());
    }

    public function test_show_ignores_unsupported_locale_query_param(): void
    {
        $token = (new SetupTokenService())->generate();

        $response = $this->get("/setup/{$token}?locale=fr");

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Setup')
            ->where('locale', null)
        );
    }

    public function test_store_redirects_to_login_when_users_already_exist(): void
    {
        User::factory()->create();
        $token = (new SetupTokenService())->generate();

        $response = $this->post("/setup/{$token}", $this->validPayload());

        $response->assertRedirect(route('login'));
        $this->assertSame(1, User::count());
    }

    public function test_store_rejects_invalid_token_with_410(): void
    {
        $response = $this->post('/setup/not-a-real-token', $this->validPayload());

        $response->assertStatus(410);
        $this->assertSame(0, User::count());
    }

    public function test_store_creates_root_user_and_logs_in_on_valid_token(): void
    {
        $token = (new SetupTokenService())->generate();

        $response = $this->post("/setup/{$token}", $this->validPayload());

        $response->assertRedirect(route('onboarding.show'));
        $this->assertAuthenticated();

        $user = User::first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->is_root);
        $this->assertSame('root.user', $user->username);
        $this->assertSame('root@example.com', $user->email);
    }

    public function test_store_persists_app_locale_setting_when_locale_given(): void
    {
        
        
        SettingValue::create(['setting_item' => 'locale', 'key' => 'app_locale', 'value' => 'en']);
        $token = (new SetupTokenService())->generate();

        $this->post("/setup/{$token}", array_merge($this->validPayload(), ['locale' => 'de']));

        $this->assertSame('de', Settings::get('app_locale'));
    }

    public function test_store_leaves_app_locale_setting_untouched_when_locale_omitted(): void
    {
        SettingValue::create(['setting_item' => 'locale', 'key' => 'app_locale', 'value' => 'en']);
        $token = (new SetupTokenService())->generate();

        $this->post("/setup/{$token}", $this->validPayload());

        $this->assertSame('en', Settings::get('app_locale'));
    }

    public function test_store_consumes_token_so_it_cannot_be_reused(): void
    {
        $token = (new SetupTokenService())->generate();

        $this->post("/setup/{$token}", $this->validPayload());

        $second = $this->post("/setup/{$token}", array_merge($this->validPayload(), [
            'username' => 'someone.else',
            'email'    => 'someone-else@example.com',
        ]));

        
        
        
        
        $second->assertRedirect('/');
        $this->assertSame(1, User::count());
    }

    protected function validPayload(): array
    {
        return [
            'first_name'            => 'Root',
            'last_name'             => 'User',
            'username'              => 'root.user',
            'email'                 => 'root@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];
    }
}
