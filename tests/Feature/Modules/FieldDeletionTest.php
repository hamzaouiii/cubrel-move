<?php

namespace Tests\Feature\Modules;

use App\Models\Layout;
use App\Models\Module as ModuleModel;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class FieldDeletionTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected ModuleModel $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $this->module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));
    }

    protected function makeLayout(string $type, array $definition): Layout
    {
        $layout = new Layout();
        $layout->module_id = $this->module->id;
        $layout->module_name = $this->module->slug;
        $layout->type = $type;
        $layout->definition = $definition;
        $layout->save();

        return $layout;
    }

    public function test_a_custom_field_can_be_deleted(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);

        $response = $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('fields', [
            'module_id' => $this->module->id,
            'name' => 'favourite_colour',
        ]);
    }

    public function test_a_stock_field_cannot_be_deleted(): void
    {
        $field = $this->makeField($this->module, ['name' => 'website', 'is_custom' => false]);

        $response = $this->delete("/settings/modules/{$this->module->id}/fields/website");

        $response->assertSessionHasErrors('field');
        $this->assertDatabaseHas('fields', ['id' => $field->id]);
    }

    public function test_deleting_a_field_removes_it_from_the_list_layout(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        $this->makeLayout('list', [
            'columns' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'modules.defaults.name'],
                ['name' => 'favourite_colour', 'type' => 'text', 'label' => 'Favourite Colour'],
            ],
        ]);

        $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $layout = Layout::where('module_id', $this->module->id)->where('type', 'list')->firstOrFail();
        $columnNames = collect($layout->definition['columns'])->pluck('name')->all();

        $this->assertSame(['name'], $columnNames);
    }

    public function test_deleting_a_field_removes_it_from_the_record_layout_sections(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        $this->makeLayout('record', [
            'sections' => [
                [
                    'name' => 'Details',
                    'layout' => [
                        ['name' => 'name'],
                        ['name' => 'favourite_colour'],
                    ],
                ],
            ],
        ]);

        $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $layout = Layout::where('module_id', $this->module->id)->where('type', 'record')->firstOrFail();
        $fieldNames = collect($layout->definition['sections'][0]['layout'])->pluck('name')->all();

        $this->assertSame(['name'], $fieldNames);
    }

    public function test_deleting_a_field_removes_it_from_the_linking_panel_layout(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        $this->makeLayout('linkingPanel', [
            'columns' => [
                ['name' => 'name'],
                ['name' => 'favourite_colour'],
            ],
        ]);

        $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $layout = Layout::where('module_id', $this->module->id)->where('type', 'linkingPanel')->firstOrFail();
        $columnNames = collect($layout->definition['columns'])->pluck('name')->all();

        $this->assertSame(['name'], $columnNames);
    }

    

    public function test_deleting_a_field_does_not_touch_the_related_layout(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        $this->makeLayout('related', [
            'columns' => [
                ['layout' => [['name' => 'favourite_colour']]],
            ],
        ]);

        $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $layout = Layout::where('module_id', $this->module->id)->where('type', 'related')->firstOrFail();

        $this->assertSame('favourite_colour', $layout->definition['columns'][0]['layout'][0]['name']);
    }

    public function test_deleting_a_field_leaves_existing_records_custom_fields_json_untouched(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        $account = Account::create(['name' => 'Acme', 'favourite_colour' => 'Blue']);

        $this->delete("/settings/modules/{$this->module->id}/fields/favourite_colour");

        $raw = DB::table('accounts')->where('id', $account->id)->value('custom_fields');
        $this->assertSame('Blue', json_decode($raw, true)['favourite_colour']);

        
        
        
        
        
        Account::clearCustomFieldCache();
        $this->assertArrayNotHasKey('favourite_colour', $account->fresh()->toArray());
    }

    public function test_records_using_count_is_shown_on_the_index_page(): void
    {
        $this->makeField($this->module, ['name' => 'favourite_colour', 'is_custom' => true]);
        Account::create(['name' => 'Has value', 'favourite_colour' => 'Blue']);
        Account::create(['name' => 'No value']);

        $response = $this->get("/settings/modules/{$this->module->id}/fields");

        $response->assertInertia(fn (Assert $page) => $page
            ->where('fields.0.records_using', 1)
        );
    }

    public function test_deleting_a_nonexistent_field_404s(): void
    {
        $response = $this->delete("/settings/modules/{$this->module->id}/fields/does_not_exist");

        $response->assertNotFound();
    }
}
