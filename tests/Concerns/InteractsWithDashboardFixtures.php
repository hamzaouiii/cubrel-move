<?php

namespace Tests\Concerns;

use App\Models\Field;
use App\Models\Module;
use App\Models\Settings\SettingValue;
use App\Models\User;

trait InteractsWithDashboardFixtures
{
    /**
     * Marks onboarding as complete so routes behind the 'onboarded'
     * middleware (EnsureOnboardingComplete) don't redirect to /onboarding.
     */
    protected function completeOnboarding(): void
    {
        SettingValue::create(['setting_item' => 'system', 'key' => 'onboarding_completed', 'value' => '1']);
    }

    /**
     * BaseModule::booted() auto-fills owner_id on every model — including User
     * itself, since User extends BaseModule. On a fully empty test database
     * there's no admin/any user to fall back to, so the very first user must
     * be created with model events suppressed (same trick DatabaseSeeder uses
     * via WithoutModelEvents for the same reason).
     */
    protected function makeUser(array $attributes = []): User
    {
        return User::withoutEvents(fn () => User::factory()->create($attributes));
    }

    /**
     * table_name defaults to match slug (the real convention in this app —
     * e.g. slug 'leads' backs the 'leads' table) since OwnershipService runs
     * raw SQL against it directly for any module with has_owner = true.
     */
    protected function makeModule(array $overrides = []): Module
    {
        $attributes = array_merge([
            'name'        => 'Leads',
            'slug'        => 'leads',
            'path'        => '/leads',
            'has_owner'   => true,
            'is_active'   => true,
        ], $overrides);

        $attributes['table_name'] ??= $attributes['slug'];

        return Module::create($attributes);
    }

    /**
     * AggregationService validates fields against Module::allFields(), which
     * reads only the `fields` DB table — it does NOT merge in config('default_fields')
     * the way builderFields() does. So even built-in-looking fields like
     * created_at need a real Field row here to be usable by metric/breakdown/timeSeries.
     */
    protected function makeField(Module $module, array $overrides = []): Field
    {
        $name = $overrides['name'] ?? 'field';

        return Field::create(array_merge([
            'module_id' => $module->id,
            'name'      => $name,
            'key'       => $module->slug.'.'.$name,
            'type'      => 'text',
            'label'     => $name,
            'filterable' => true,
        ], $overrides));
    }
}
