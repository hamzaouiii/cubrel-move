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

/**
 * Covers RelationshipManagerController's route surface. Relationships are
 * deliberately not editable after creation (only create/list/delete) — the
 * resource route used to be registered unscoped, so 'edit'/'update' resolved
 * to controller methods that don't exist (a 500 if ever hit, not a clean
 * 404). routes/web.php now scopes it to
 * ->only(['index','create','store','destroy']) to match the controller's
 * actual methods. See FEATURES.md §6 Relationships.
 */
class RelationshipManagerRouteTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();
        $this->actingAs($this->makeUser(['is_admin' => true]));

        // Required by RelationshipManagerController@create's firstOrFail() lookup.
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

        // The URI still matches the DELETE route registered for the same
        // path, so an unsupported method there is a 405 (method not
        // allowed), not a 404 — Laravel only 404s once no route matches the
        // URI at all, regardless of method.
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

    /**
     * Regression test: cleanupRelationshipPanels() used to only strip the deleted
     * relationship's 'related' panel from the requesting module's own layout,
     * leaving a stale panel reference on the other side. A relationship panel is
     * normally configured on both sides (Accounts shows a "Deals" panel, Deals
     * shows an "Account" panel), so deleting it must clean up both.
     */
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

        // module_id isn't in Layout::$fillable — LayoutManagerController sets it via
        // direct property assignment (bypassing mass-assignment guarding), so tests
        // creating a Layout fixture must do the same rather than pass it to create().
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

        // Deleted from Accounts' own settings page — Deals' layout isn't the
        // requesting module, but must still be cleaned up.
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

    /**
     * 'many-to-one' isn't stored as its own type — it's 'one-to-many' with
     * left/right swapped, since left_module is always resolved to whichever
     * module's settings page the request came from. Creating "many deals to
     * one account" from Deals' own page must end up stored identically to
     * creating "one account to many deals" from Accounts' page.
     */
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
