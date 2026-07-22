<?php

namespace Tests\Feature;

use App\Models\Settings\SettingValue;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class PreferencesControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/preferences')->assertRedirect('/login');
    }

    public function test_index_renders_tabs_and_theme_options_from_config(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/preferences')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Preferences/Index')
                ->where('tabs', config('preferences.tabs'))
                ->where('themeOptions', config('preferences.theme_options'))
            );
    }

    /**
     * Every overridable field declared in config/preferences.php: saving it
     * persists a personal override, the merged `appSettings` Inertia share
     * (built in AppServiceProvider) reflects it immediately, and clearing
     * it (null) removes the override again. Looped over the config file
     * itself rather than hardcoded per key so this test can't silently
     * drift out of sync with config/preferences.php.
     */
    public function test_every_configured_field_can_be_overridden_then_reset(): void
    {
        $user = $this->makeUser();

        foreach (config('preferences.tabs') as $tabKey => $tab) {
            foreach ($tab['fields'] as $key => $field) {
                $value = $this->sampleValueFor($field['type']);

                $this->actingAs($user)
                    ->put('/preferences', [$key => $value])
                    ->assertSessionHasNoErrors();

                $user->refresh();
                $this->assertSame(
                    $value,
                    $user->preferences[$key] ?? null,
                    "Override for [{$tabKey}.{$key}] was not persisted on the user."
                );

                $this->actingAs($user)
                    ->get('/preferences')
                    ->assertInertia(fn (Assert $page) => $page
                        ->where("appSettings.{$key}", $value)
                    );

                $this->actingAs($user)
                    ->put('/preferences', [$key => null])
                    ->assertSessionHasNoErrors();

                $user->refresh();
                $this->assertArrayNotHasKey(
                    $key,
                    $user->preferences ?? [],
                    "Override for [{$tabKey}.{$key}] was not cleared after resetting to system default."
                );
            }
        }
    }

    public function test_app_locale_override_changes_the_active_app_locale(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->put('/preferences', ['app_locale' => 'de'])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)->get('/preferences');

        $this->assertSame('de', App::getLocale());
    }

    /**
     * Any field whose config/preferences.php validation rule restricts it
     * to a fixed set of values (e.g. app_locale's "in:en,de") must reject
     * anything outside that set. Driven by config rather than hardcoding
     * which fields have an enum constraint, so it stays correct if that
     * changes (e.g. a field's "in:" list is edited or removed).
     */
    public function test_fields_with_enum_validation_reject_invalid_values(): void
    {
        $user = $this->makeUser();

        foreach (config('preferences.tabs') as $tabKey => $tab) {
            foreach ($tab['fields'] as $key => $field) {
                if (! str_contains($field['validation'], 'in:')) {
                    continue;
                }

                $this->actingAs($user)
                    ->put('/preferences', [$key => 'not-a-valid-option'])
                    ->assertSessionHasErrors($key);

                $user->refresh();
                $this->assertArrayNotHasKey(
                    $key,
                    $user->preferences ?? [],
                    "Invalid value for [{$tabKey}.{$key}] was persisted despite failing validation."
                );
            }
        }
    }

    /**
     * The three "Lists & Panels" settings are consumed server-side (via
     * Settings::getPersonal()) to size real pagination, unlike the purely
     * display-oriented fields which only flow through the appSettings
     * Inertia share. Locks in the fix: getPersonal() honors the override,
     * while plain get() (used by e.g. PDF rendering) stays System-only.
     */
    public function test_panel_limit_overrides_affect_settings_get_personal_but_not_plain_get(): void
    {
        $user = $this->makeUser();

        SettingValue::create([
            'setting_item' => 'display-defaults',
            'key' => 'related_panel_limit',
            'value' => '5',
        ]);

        $this->actingAs($user)
            ->put('/preferences', ['related_panel_limit' => 25])
            ->assertSessionHasNoErrors();

        $this->assertSame(25, Settings::getPersonal('related_panel_limit'));
        $this->assertSame('5', Settings::get('related_panel_limit'));
    }

    public function test_panel_limit_override_is_ignored_for_guests(): void
    {
        SettingValue::create([
            'setting_item' => 'display-defaults',
            'key' => 'list_view_limit',
            'value' => '31',
        ]);

        $this->assertSame('31', Settings::getPersonal('list_view_limit'));
    }

    private function sampleValueFor(string $type): mixed
    {
        return match ($type) {
            'lang_switcher' => 'de',
            'theme_switcher' => 'dark',
            'date' => 'd.m.Y',
            'datetime' => 'd.m.Y H:i',
            'color' => '#123ABC',
            'int' => 42,
            'bool' => true,
            default => 'sample-value',
        };
    }
}
