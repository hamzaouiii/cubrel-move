<?php

namespace Tests\Feature\Modules;

use App\Models\DropdownList;
use App\Models\Layout;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Relationship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class RelationshipManagerRouteTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
        $this->actingAs($this->makeUser(['is_admin' => true]));

        
        DropdownList::create([
            'key' => 'relationship_type_list',
            'values' => [
                ['label' => 'relationships.types.one-to-one', 'value' => 'one-to-one'],
                ['label' => 'relationships.types.one-to-many', 'value' => 'one-to-many'],
                ['label' => 'relationships.types.many-to-many', 'value' => 'many-to-many'],
            ],
        ]);
    }

    public function test_edit_and_update_routes_are_not_registered(): void
    {
        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $relationship = Relationship::create([
            'name' => 'accounts_contacts',
            'label' => 'Contacts',
            'left_module' => 'accounts',
            'right_module' => 'contacts',
            'type' => 'many-to-many',
            'is_system' => false,
        ]);

        $this->get("/settings/modules/{$module->id}/relationships/{$relationship->id}/edit")
            ->assertNotFound();

        
        
        
        
        $this->put("/settings/modules/{$module->id}/relationships/{$relationship->id}", [
            'label' => 'Renamed',
        ])->assertMethodNotAllowed();
    }

    public function test_index_create_store_and_destroy_still_work(): void
    {
        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);
        $this->makeModule([
            'slug' => 'contacts',
            'name' => 'Contacts',
            'path' => '/contacts',
            'model_class' => Contact::class,
            'table_name' => 'contacts',
        ]);

        $this->get("/settings/modules/{$module->id}/relationships")->assertSuccessful();
        $this->get("/settings/modules/{$module->id}/relationships/create")->assertSuccessful();

        $this->post("/settings/modules/{$module->id}/relationships", [
            'name' => 'accounts_contacts',
            'label' => 'Contacts',
            'right_module' => 'contacts',
            'type' => 'many-to-many',
        ])->assertRedirect();

        $relationship = Relationship::where('name', 'accounts_contacts')->firstOrFail();

        $this->delete("/settings/modules/{$module->id}/relationships/{$relationship->id}")
            ->assertRedirect();

        $this->assertModelMissing($relationship);
    }

    

    public function test_deleting_a_relationship_cleans_up_related_panels_on_both_sides(): void
    {
        $accounts = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);
        $deals = $this->makeModule([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
        ]);

        $relationship = Relationship::create([
            'name' => 'accounts_deals',
            'label' => 'Deals',
            'left_module' => 'accounts',
            'right_module' => 'deals',
            'type' => 'one-to-many',
            'is_system' => false,
        ]);

        
        
        
        $accountsLayout = new Layout([
            'module_name' => 'accounts',
            'type' => 'related',
            'definition' => [
                'columns' => [
                    ['layout' => [['name' => 'accounts_deals'], ['name' => 'other_relationship']]],
                ],
            ],
        ]);
        $accountsLayout->module_id = $accounts->id;
        $accountsLayout->save();

        $dealsLayout = new Layout([
            'module_name' => 'deals',
            'type' => 'related',
            'definition' => [
                'columns' => [
                    ['layout' => [['name' => 'accounts_deals']]],
                ],
            ],
        ]);
        $dealsLayout->module_id = $deals->id;
        $dealsLayout->save();

        
        
        $this->delete("/settings/modules/{$accounts->id}/relationships/{$relationship->id}")
            ->assertRedirect();

        $this->assertSame(
            ['other_relationship'],
            collect($accountsLayout->fresh()->definition['columns'][0]['layout'])->pluck('name')->all(),
        );
        $this->assertSame(
            [],
            $dealsLayout->fresh()->definition['columns'][0]['layout'],
        );
    }

    public function test_system_relationships_cannot_be_deleted(): void
    {
        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $relationship = Relationship::create([
            'name' => 'accounts_contacts_system',
            'label' => 'Contacts',
            'left_module' => 'accounts',
            'right_module' => 'contacts',
            'type' => 'many-to-many',
            'is_system' => true,
        ]);

        $this->delete("/settings/modules/{$module->id}/relationships/{$relationship->id}")
            ->assertSessionHasErrors('rel');

        $this->assertModelExists($relationship);
    }

    

    public function test_many_to_one_is_stored_as_one_to_many_with_modules_swapped(): void
    {
        $deals = $this->makeModule([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
        ]);
        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
        ]);

        $this->post("/settings/modules/{$deals->id}/relationships", [
            'name' => 'deals_accounts',
            'label' => 'Account',
            'right_module' => 'accounts',
            'type' => 'many-to-one',
        ])->assertRedirect();

        $relationship = Relationship::where('name', 'deals_accounts')->firstOrFail();

        $this->assertSame('accounts', $relationship->left_module);
        $this->assertSame('deals', $relationship->right_module);
        $this->assertSame('one-to-many', $relationship->type);
    }

    public function test_many_to_one_duplicate_is_still_caught_after_swap(): void
    {
        $deals = $this->makeModule([
            'slug' => 'deals',
            'name' => 'Deals',
            'path' => '/deals',
        ]);
        $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
        ]);

        Relationship::create([
            'name' => 'accounts_deals_existing',
            'label' => 'Deals',
            'left_module' => 'accounts',
            'right_module' => 'deals',
            'type' => 'one-to-many',
            'is_system' => false,
        ]);

        $this->post("/settings/modules/{$deals->id}/relationships", [
            'name' => 'deals_accounts_new',
            'label' => 'Account',
            'right_module' => 'accounts',
            'type' => 'many-to-one',
        ])->assertSessionHasErrors('right_module');
    }
}
