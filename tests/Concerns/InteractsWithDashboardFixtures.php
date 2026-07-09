<?php

namespace Tests\Concerns;

use App\Models\Field;
use App\Models\Module;
use App\Models\Settings\SettingValue;
use App\Models\User;

trait InteractsWithDashboardFixtures
{
    

    protected function completeOnboarding(): void
    {
        SettingValue::create(['setting_item' => 'system', 'key' => 'onboarding_completed', 'value' => '1']);
    }

    

    protected function makeUser(array $attributes = []): User
    {
        return User::withoutEvents(fn () => User::factory()->create($attributes));
    }

    

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
