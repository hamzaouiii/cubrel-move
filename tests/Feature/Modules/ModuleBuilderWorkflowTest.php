<?php

namespace Tests\Feature\Modules;

use App\Models\DropdownList;
use App\Models\Field;
use App\Models\Icon;
use App\Models\Label;
use App\Models\Module;
use App\Models\Settings\SettingValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class ModuleBuilderWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    private array $cleanupTables = [];
    private array $cleanupFileBaseNames = [];
    private array $cleanupModuleIds = [];
    private array $cleanupUserIds = [];
    private ?string $cleanupDropdownListId = null;
    private ?int $cleanupIconId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);

        
        
        
        $dropdown = DropdownList::create([
            'key' => 'module_category_list',
            'values' => [['label' => 'Sales', 'value' => 'sales', 'status' => 'success']],
        ]);
        $this->cleanupDropdownListId = $dropdown->id;

        
        
        
        $icon = Icon::create(['name' => 'cube', 'style' => 'solid', 'class' => 'fa-solid fa-cube']);
        $this->cleanupIconId = $icon->id;
    }

    protected function tearDown(): void
    {
        foreach ($this->cleanupTables as $table) {
            Schema::dropIfExists($table);
        }

        foreach ($this->cleanupFileBaseNames as $baseName) {
            @unlink(app_path("Models/Modules/Custom/{$baseName}.php"));
            @unlink(app_path("Handlers/Modules/Custom/{$baseName}ModuleHandler.php"));
        }

        foreach ($this->cleanupModuleIds as $moduleId) {
            Field::where('module_id', $moduleId)->delete();
            Label::where('module_id', $moduleId)->delete();
            Module::withoutGlobalScopes()->where('id', $moduleId)->delete();
        }

        foreach ($this->cleanupUserIds as $userId) {
            User::where('id', $userId)->delete();
        }

        if ($this->cleanupDropdownListId) {
            DropdownList::where('id', $this->cleanupDropdownListId)->delete();
        }

        if ($this->cleanupIconId) {
            Icon::where('id', $this->cleanupIconId)->delete();
        }

        parent::tearDown();
    }

    protected function makeAdmin(): User
    {
        $admin = $this->makeUser(['is_admin' => true, 'type' => 'admin']);
        $this->cleanupUserIds[] = $admin->id;
        $this->actingAs($admin);

        return $admin;
    }

    

    protected function deployCustomModule(string $slugSuffix, array $draftFields = []): Module
    {
        $slug = 'cstm_test_'.$slugSuffix;

        
        $this->get('/settings/modulebuilder')->assertOk();
        $draft = Module::where('is_draft', true)->where('locked_by', auth()->id())->firstOrFail();
        $this->cleanupModuleIds[] = $draft->id;

        
        $this->put("/settings/modulebuilder/{$draft->id}", [
            'display_label' => 'Widgets '.$slugSuffix,
            'single_label' => 'Widget '.$slugSuffix,
            'slug' => $slug,
            'category' => 'sales',
            'show_in_sidebar' => true,
            'has_line_items' => false,
            'has_owner' => true,
        ])->assertRedirect();

        
        
        
        
        
        
        foreach ($draftFields as $fieldDef) {
            $this->post("/settings/modulebuilder/{$draft->id}/field", array_merge([
                'dropdown_list' => null,
                'readonly' => false,
                'required' => false,
                'sortable' => false,
                'default_value' => null,
                'min_length' => null,
                'max_length' => null,
                'regex' => null,
                'key' => 'draft_'.$fieldDef['name'],
            ], $fieldDef))->assertRedirect();
        }

        
        
        
        
        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/initialize", [
            'display_label' => 'Widgets '.$slugSuffix,
            'single_label' => 'Widget '.$slugSuffix,
            'slug' => $slug,
            'category' => 'sales',
            'show_in_sidebar' => true,
        ])->assertOk();

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/generate-files")->assertOk();
        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/create-labels")->assertOk();

        $module = $draft->fresh();
        $baseName = class_basename($module->model_class);
        $this->cleanupFileBaseNames[] = $baseName;

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/create-table")->assertOk();
        $this->cleanupTables[] = $module->fresh()->table_name;

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/activate-fields")->assertOk();

        return $module->fresh();
    }

    public function test_creates_and_scaffolds_new_custom_module_with_defined_field(): void
    {
        $this->makeAdmin();
        $slug = 'widgets_'.Str::random(6);

        $module = $this->deployCustomModule($slug, [[
            'label' => 'Budget',
            'name' => 'budget',
            'type' => 'number',
        ]]);

        $this->assertTrue((bool) $module->is_active);
        $this->assertFalse((bool) $module->is_draft);

        $baseName = class_basename($module->model_class);
        $this->assertFileExists(app_path("Models/Modules/Custom/{$baseName}.php"));
        $this->assertFileExists(app_path("Handlers/Modules/Custom/{$baseName}ModuleHandler.php"));
        $this->assertTrue(class_exists($module->model_class));
        $this->assertTrue(class_exists($module->handler_class));

        $this->assertTrue(Schema::hasTable($module->table_name));
        
        
        
        $this->assertTrue(Schema::hasColumn($module->table_name, 'budget'));
        $this->assertFalse(Schema::hasColumn($module->table_name, 'draft_budget'));

        $field = Field::where('module_id', $module->id)->where('name', 'budget')->firstOrFail();
        
        
        $this->assertFalse((bool) $field->is_custom);
        $this->assertFalse((bool) $field->is_draft);
        
        
        
        $this->assertSame("{$module->slug}_budget", $field->key);
        $this->assertTrue((bool) $field->is_active);
    }

    public function test_adds_custom_field_to_deployed_custom_module(): void
    {
        $this->makeAdmin();
        $slug = 'widgets_'.Str::random(6);
        $module = $this->deployCustomModule($slug);

        $response = $this->post("/settings/modules/{$module->id}/fields/create", [
            'label' => 'Favourite Colour',
            'name' => 'favourite_colour',
            
            
            'key' => 'draft_favourite_colour',
            'type' => 'text',
            'dropdown_list' => null,
            'readonly' => false,
            'required' => false,
            'sortable' => false,
            'default_value' => null,
            'min_length' => null,
            'max_length' => null,
            'regex' => null,
            'related_module' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fields', [
            'module_id' => $module->id,
            'name' => 'favourite_colour',
            'is_custom' => 1,
        ]);
    }

    public function test_crud_on_custom_module_including_custom_field_values(): void
    {
        $this->makeAdmin();
        $slug = 'widgets_'.Str::random(6);

        $module = $this->deployCustomModule($slug, [[
            'label' => 'Budget',
            'name' => 'budget',
            'type' => 'number',
        ]]);

        
        $this->post("/settings/modules/{$module->id}/fields/create", [
            'label' => 'Favourite Colour',
            'name' => 'favourite_colour',
            'key' => 'draft_favourite_colour',
            'type' => 'text',
            'dropdown_list' => null,
            'readonly' => false,
            'required' => false,
            'sortable' => false,
            'default_value' => null,
            'min_length' => null,
            'max_length' => null,
            'regex' => null,
            'related_module' => null,
        ])->assertRedirect();

        
        
        if (! Module::withoutGlobalScopes()->where('slug', 'line_items')->exists()) {
            $lineItems = $this->makeModule(['slug' => 'line_items', 'name' => 'Line Items', 'path' => '/line_items', 'has_owner' => false]);
            $this->cleanupModuleIds[] = $lineItems->id;
        }
        if (! Module::withoutGlobalScopes()->where('slug', 'products')->exists()) {
            $products = $this->makeModule(['slug' => 'products', 'name' => 'Products', 'path' => '/products', 'has_owner' => true]);
            $this->cleanupModuleIds[] = $products->id;
        }

        
        
        
        
        $this->post("/{$module->slug}", [
            'name' => 'First Widget',
            'budget' => 500,
            'favourite_colour' => 'Blue',
        ])->assertRedirect();

        $modelClass = $module->model_class;
        $record = $modelClass::where('name', 'First Widget')->firstOrFail();

        $raw = $modelClass::where('name', 'First Widget')->toBase()->first();
        $this->assertEquals(500, $raw->budget ?? null, 'Real column `budget` should hold the definition-time field\'s value.');
        $this->assertSame('Blue', $record->favourite_colour);

        
        $this->get("/{$module->slug}")->assertOk();

        
        $this->get("/{$module->slug}/{$record->id}")->assertOk();

        
        $this->put("/{$module->slug}/{$record->id}", [
            'name' => 'First Widget',
            'budget' => 750,
            'favourite_colour' => 'Green',
        ])->assertRedirect();

        $record->refresh();
        $this->assertSame('Green', $record->favourite_colour);
        $this->assertEquals(750, $modelClass::where('id', $record->id)->toBase()->first()->budget);

        
        $this->delete("/{$module->slug}/{$record->id}")->assertRedirect();
        $this->assertDatabaseMissing($module->table_name, ['id' => $record->id]);
    }
}
