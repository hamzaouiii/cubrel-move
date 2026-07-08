<?php

namespace Tests\Feature\Modules;

use App\Models\DropdownList;
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
}
