<?php

namespace Tests\Feature\Modules;

use App\Models\Layout;
use App\Models\Module;
use App\Models\Modules\Account;
use App\Models\Modules\Deal;
use App\Models\Relationship;
use App\Services\Relationships\RelationshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Regression coverage for the bug where deactivating (or deleting) a module
 * left its relationships and related-panel layout entries dangling on other
 * modules, corrupting their record pages. See RelationshipService::getRelationshipForModule()
 * and DeleteModule/DeactivateModule.
 */
class ModuleDeactivationRelationshipTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $this->makeModule([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
            'model_class' => Deal::class,
            'table_name' => 'deals',
        ]);
        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));
    }

    protected function createRelationship(): Relationship
    {
        $dealsModule = Module::where('slug', 'deals')->firstOrFail();

        $this->post("/settings/modules/{$dealsModule->id}/relationships", [
            'name' => 'deals_accounts',
            'label' => 'Account',
            'right_module' => 'accounts',
            'type' => 'many-to-one',
        ])->assertRedirect();

        return Relationship::where('name', 'deals_accounts')->firstOrFail();
    }

    public function test_deactivating_the_related_module_hides_the_relationship_and_restores_it_on_reactivation(): void
    {
        $this->createRelationship();

        $this->assertTrue(RelationshipService::getRelationshipForModule('deals')->contains('name', 'deals_accounts'));

        $this->artisan('modules:deactivate', ['slug' => 'accounts'])->assertSuccessful();
        RelationshipService::clearCache();

        $this->assertFalse(RelationshipService::getRelationshipForModule('deals')->contains('name', 'deals_accounts'));

        Module::where('slug', 'accounts')->update(['is_active' => true]);
        RelationshipService::clearCache();

        $this->assertTrue(RelationshipService::getRelationshipForModule('deals')->contains('name', 'deals_accounts'));
    }

    public function test_deactivating_the_related_module_hides_it_from_the_related_panels_of_a_linked_record(): void
    {
        $relationship = $this->createRelationship();

        $deal = Deal::create(['name' => 'Big Deal']);
        $account = Account::create(['name' => 'Acme Inc']);

        $this->post("/modules/deals/{$deal->id}/relationships/deals_accounts", [
            'related_ids' => [(string) $account->id],
        ])->assertSuccessful();

        $related = RelationshipService::getAllRelatedRecords('deals', (string) $deal->id);
        $this->assertArrayHasKey($relationship->name, $related->toArray());

        $this->artisan('modules:deactivate', ['slug' => 'accounts'])->assertSuccessful();
        RelationshipService::clearCache();

        $related = RelationshipService::getAllRelatedRecords('deals', (string) $deal->id);
        $this->assertArrayNotHasKey($relationship->name, $related->toArray());
    }

    public function test_deleting_a_module_cleans_up_related_panel_layout_entries_on_the_other_module(): void
    {
        $relationship = $this->createRelationship();
        $dealsModule = Module::where('slug', 'deals')->firstOrFail();

        // cleanupRelationshipPanels() matches layouts by module_id, not module_name,
        // which isn't mass-assignable — set it directly after instantiation.
        $layout = new Layout([
            'module_name' => 'deals',
            'type' => 'related',
            'name' => 'related',
            'definition' => [
                'columns' => [
                    ['layout' => [['name' => 'deals_accounts'], ['name' => 'some_other_relationship']]],
                ],
            ],
        ]);
        $layout->module_id = $dealsModule->id;
        $layout->save();

        // --keep-table: 'accounts' is a real, shared migration-backed table (mysql DDL
        // implicitly commits, so RefreshDatabase's rollback can't undo a drop of it).
        $this->artisan('modules:delete', ['slug' => 'accounts', '--force' => true, '--keep-table' => true])->assertSuccessful();

        $layout = Layout::where('module_name', 'deals')->where('type', 'related')->firstOrFail();
        $panelNames = collect($layout->definition['columns'][0]['layout'])->pluck('name');

        $this->assertFalse($panelNames->contains('deals_accounts'));
        $this->assertTrue($panelNames->contains('some_other_relationship'));
        $this->assertSame(0, Relationship::where('id', $relationship->id)->count());
    }
}
