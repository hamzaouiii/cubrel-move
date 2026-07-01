<?php

namespace Tests\Feature;

use App\Models\Dashboard;
use App\Models\Module;
use App\Models\Modules\Lead;
use App\Models\Relationship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function makeLeadsModule(array $overrides = []): Module
    {
        return $this->makeModule(array_merge(['model_class' => Lead::class], $overrides));
    }

    public function test_regular_user_only_sees_own_records_by_default(): void
    {
        $this->makeLeadsModule();

        $owner = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);
        $other = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        Lead::factory()->count(2)->create(['owner_id' => $owner->id]);
        Lead::factory()->count(3)->create(['owner_id' => $other->id]);

        $response = $this->actingAs($owner)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => ['module' => 'leads', 'aggregate' => 'count'],
        ]);

        $response->assertOk()->assertJson(['value' => 2]);
    }

    public function test_regular_user_can_opt_out_with_show_all_records(): void
    {
        $this->makeLeadsModule();

        $owner = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);
        $other = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        Lead::factory()->count(2)->create(['owner_id' => $owner->id]);
        Lead::factory()->count(3)->create(['owner_id' => $other->id]);

        $response = $this->actingAs($owner)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => ['module' => 'leads', 'aggregate' => 'count', 'showAllRecords' => true],
        ]);

        $response->assertOk()->assertJson(['value' => 5]);
    }

    public function test_admin_sees_all_records_regardless_of_toggle(): void
    {
        $this->makeLeadsModule();

        $admin = $this->makeUser(['is_admin' => true, 'type' => 'admin']);
        $other = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        Lead::factory()->count(1)->create(['owner_id' => $admin->id]);
        Lead::factory()->count(4)->create(['owner_id' => $other->id]);

        $response = $this->actingAs($admin)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => ['module' => 'leads', 'aggregate' => 'count'],
        ]);

        $response->assertOk()->assertJson(['value' => 5]);
    }

    public function test_org_wide_type_sees_all_records_by_default(): void
    {
        $this->makeLeadsModule();

        $manager = $this->makeUser(['is_admin' => false, 'type' => 'sales_manager']);
        $rep     = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        Lead::factory()->count(2)->create(['owner_id' => $manager->id]);
        Lead::factory()->count(3)->create(['owner_id' => $rep->id]);

        $response = $this->actingAs($manager)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => ['module' => 'leads', 'aggregate' => 'count'],
        ]);

        $response->assertOk()->assertJson(['value' => 5]);
    }

    public function test_disallowed_filter_field_is_rejected(): void
    {
        $this->makeLeadsModule();

        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => [
                'module'    => 'leads',
                'aggregate' => 'count',
                'filters'   => [
                    ['field' => 'password', 'operator' => 'equals', 'value' => 'x'],
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_widget_type_is_rejected(): void
    {
        $this->makeLeadsModule();

        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->postJson('/dashboard/widget-data', [
            'type'   => 'pie-in-the-sky',
            'config' => ['module' => 'leads'],
        ]);

        $response->assertStatus(422);
    }

    public function test_missing_module_is_rejected(): void
    {
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->postJson('/dashboard/widget-data', [
            'type'   => 'metric',
            'config' => ['module' => 'does-not-exist', 'aggregate' => 'count'],
        ]);

        $response->assertStatus(422);
    }

    public function test_index_returns_expected_shared_props(): void
    {
        // OwnershipService caches its module→table map forever under a fixed key;
        // the array cache store persists for the whole PHPUnit process, so a stale
        // map from an earlier test's (now rolled-back) modules can leak in here.
        Cache::flush();

        $this->makeLeadsModule();
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('dashboardLayout')
            ->has('dashboardModules')
            ->where('dashboardConfig.widget_types', config('dashboard.widget_types'))
            ->has('filterOperators.by_type')
        );
    }

    public function test_module_fields_returns_field_metadata(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text', 'label' => 'Company']);
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->getJson('/dashboard/module-fields/leads');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'company', 'label' => 'Company', 'type' => 'text']);
    }

    public function test_filterable_fields_excludes_non_filterable_custom_fields(): void
    {
        $module = $this->makeLeadsModule();
        $this->makeField($module, ['name' => 'company', 'type' => 'text', 'filterable' => true]);
        $this->makeField($module, ['name' => 'internal_note', 'type' => 'text', 'filterable' => false]);
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->getJson('/dashboard/filterable-fields/leads');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'company']);
        $response->assertJsonMissing(['name' => 'internal_note']);
    }

    public function test_save_layout_creates_then_updates_dashboard(): void
    {
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $layout = [['instanceId' => 'abc', 'type' => 'metric', 'cols' => 1, 'config' => ['module' => 'leads']]];

        $response = $this->actingAs($user)->postJson('/dashboard/layout', ['layout' => $layout]);

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('dashboards', ['user_id' => $user->id, 'name' => 'My Dashboard']);
        // assertEquals, not assertSame: MySQL's JSON column type doesn't
        // guarantee key order on round-trip, only the key/value pairs.
        $this->assertEquals($layout, Dashboard::where('user_id', $user->id)->first()->layout);

        $newLayout = [['instanceId' => 'xyz', 'type' => 'breakdown', 'cols' => 2, 'config' => ['module' => 'deals']]];
        $this->actingAs($user)->postJson('/dashboard/layout', ['layout' => $newLayout]);

        $this->assertSame(1, Dashboard::where('user_id', $user->id)->count());
        $this->assertEquals($newLayout, Dashboard::where('user_id', $user->id)->first()->layout);
    }

    public function test_save_layout_requires_array(): void
    {
        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->postJson('/dashboard/layout', ['layout' => 'not-an-array']);

        $response->assertStatus(422);
    }

    public function test_people_widget_returns_leaderboard_via_http(): void
    {
        $leads = $this->makeLeadsModule();
        $this->makeField($leads, ['name' => 'owner_id', 'type' => 'record', 'related_module' => 'users']);
        $this->makeModule([
            'slug' => 'users', 'name' => 'Users', 'path' => '/users',
            'has_owner' => false, 'model_class' => User::class,
        ]);

        $rep = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);
        Lead::factory()->count(2)->create(['owner_id' => $rep->id]);

        $response = $this->actingAs($rep)->postJson('/dashboard/widget-data', [
            'type'   => 'people',
            'config' => ['module' => 'leads', 'relationField' => 'owner_id', 'aggregate' => 'count'],
        ]);

        $response->assertOk();
        $response->assertJson(['peopleModuleSlug' => 'users', 'aggregate' => 'count']);
        $response->assertJsonFragment(['id' => $rep->id, 'value' => 2.0]);
    }

    public function test_module_relationships_returns_relationship_metadata(): void
    {
        $this->makeModule(['slug' => 'contacts', 'name' => 'Contacts', 'path' => '/contacts']);
        $this->makeModule(['slug' => 'invoices', 'name' => 'Invoices', 'path' => '/invoices', 'has_owner' => false]);

        Relationship::create([
            'name'         => 'contacts_invoices',
            'label'        => 'relationships.contacts_invoices',
            'left_module'  => 'contacts',
            'right_module' => 'invoices',
            'type'         => 'one-to-many',
        ]);

        $user = $this->makeUser(['is_admin' => false, 'type' => 'sales_rep']);

        $response = $this->actingAs($user)->getJson('/dashboard/module-relationships/invoices');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'contacts_invoices', 'related_slug' => 'contacts']);
    }
}
