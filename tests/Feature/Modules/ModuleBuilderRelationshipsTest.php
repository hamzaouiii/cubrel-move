<?php

namespace Tests\Feature\Modules;

use App\Models\Field;
use App\Models\Icon;
use App\Models\Label;
use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Modules\Account;
use App\Models\Relationship;
use App\Models\RelationshipLink;
use App\Models\Settings\SettingValue;
use App\Models\User;
use App\Services\Relationships\RelationshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

/**
 * Covers relationships between modules actually produced by the module
 * builder deploy pipeline (real scaffolded model/table, not just an
 * is_custom flag on a Module row) — every relationship type
 * (one-to-one, one-to-many, many-to-one, many-to-many), created and linked
 * from both sides, for both a custom<->non-custom pairing and a
 * custom<->custom pairing. See RelationshipManyToOneLinkingTest and
 * RelationshipManagerRouteTest for the equivalent lightweight (non-deployed)
 * relationship coverage.
 *
 * IMPORTANT: same caveat as ModuleBuilderWorkflowTest — deploying a module
 * runs real CREATE TABLE DDL, which MySQL implicitly commits, so
 * RefreshDatabase's rollback can't undo anything in a test that reaches
 * deploy. Every side effect (files, tables, Module/Field/Label rows,
 * Relationship rows — which cascade-delete their RelationshipLink rows —
 * and records inserted into the pre-existing 'accounts' table) is tracked
 * and removed manually in tearDown().
 */
class ModuleBuilderRelationshipsTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    private array $cleanupTables = [];
    private array $cleanupFileBaseNames = [];
    private array $cleanupModuleIds = [];
    private array $cleanupUserIds = [];
    private array $cleanupRelationshipIds = [];
    private array $cleanupAccountIds = [];
    private ?string $cleanupModuleCategoryId = null;
    private ?int $cleanupIconId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);

        $category = ModuleCategory::create(['label' => 'Sales', 'sort_order' => 1]);
        $this->cleanupModuleCategoryId = $category->id;

        $icon = Icon::create(['name' => 'cube', 'style' => 'solid', 'class' => 'fa-solid fa-cube']);
        $this->cleanupIconId = $icon->id;
    }

    protected function tearDown(): void
    {
        if (! empty($this->cleanupRelationshipIds)) {
            Relationship::whereIn('id', $this->cleanupRelationshipIds)->delete();
        }

        if (! empty($this->cleanupAccountIds)) {
            Account::whereIn('id', $this->cleanupAccountIds)->delete();
        }

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

        if ($this->cleanupModuleCategoryId) {
            ModuleCategory::where('id', $this->cleanupModuleCategoryId)->delete();
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

    /**
     * Runs the real builder-to-deployed-module HTTP pipeline (draft, define
     * fields, deploy) and returns the resulting active Module — copied from
     * ModuleBuilderWorkflowTest::deployCustomModule so this class doesn't
     * depend on another test class's private helper.
     */
    protected function deployCustomModule(string $slugSuffix): Module
    {
        $slug = 'cstm_rel_'.$slugSuffix;

        $this->get('/settings/modulebuilder')->assertOk();
        $draft = Module::where('is_draft', true)->where('locked_by', auth()->id())->firstOrFail();
        $this->cleanupModuleIds[] = $draft->id;

        $this->put("/settings/modulebuilder/{$draft->id}", [
            'display_label' => 'Widgets '.$slugSuffix,
            'single_label' => 'Widget '.$slugSuffix,
            'slug' => $slug,
            'module_category_id' => $this->cleanupModuleCategoryId,
            'show_in_sidebar' => true,
            'has_line_items' => false,
            'has_owner' => true,
        ])->assertRedirect();

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/initialize", [
            'display_label' => 'Widgets '.$slugSuffix,
            'single_label' => 'Widget '.$slugSuffix,
            'slug' => $slug,
            'module_category_id' => $this->cleanupModuleCategoryId,
            'show_in_sidebar' => true,
        ])->assertOk();

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/generate-files")->assertOk();
        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/create-labels")->assertOk();

        $module = $draft->fresh();
        $this->cleanupFileBaseNames[] = class_basename($module->model_class);

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/create-table")->assertOk();
        $this->cleanupTables[] = $module->fresh()->table_name;

        $this->postJson("/settings/modulebuilder/{$draft->id}/deploy/activate-fields")->assertOk();

        return $module->fresh();
    }

    /**
     * Registers the pre-existing 'accounts' table/model as a non-custom
     * Module, the same way RelationshipManyToOneLinkingTest does — no DDL
     * involved, the table already exists.
     */
    protected function makeNonCustomAccountsModule(): Module
    {
        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'table_name' => 'accounts',
            'is_custom' => false,
        ]);
        $this->cleanupModuleIds[] = $module->id;

        return $module;
    }

    protected function createRecordFor(Module $module): mixed
    {
        $modelClass = $module->model_class;
        $record = $modelClass::create(['name' => $module->slug.' record '.Str::random(6)]);

        if ($module->slug === 'accounts') {
            $this->cleanupAccountIds[] = $record->id;
        }

        return $record;
    }

    /**
     * Creates a relationship from $requestingModule's settings page (as the
     * user would, picking $type and $otherModule as the related module),
     * then links two fresh records through it and asserts the link/role/
     * visibility are correct from BOTH sides — not just the side that
     * initiated the request. Handles 'many-to-one' transparently: the
     * controller swaps it to a stored 'one-to-many' with left/right
     * reversed, and this helper re-derives left/right from the persisted
     * relationship rather than assuming $requestingModule stays "left".
     */
    protected function assertRelationshipWorksBothWays(
        Module $requestingModule,
        Module $otherModule,
        string $type,
        string $relationshipName,
    ): Relationship {
        $this->post("/settings/modules/{$requestingModule->id}/relationships", [
            'name' => $relationshipName,
            'label' => $otherModule->name,
            'right_module' => $otherModule->slug,
            'type' => $type,
        ])->assertRedirect();

        $relationship = Relationship::where('name', $relationshipName)->firstOrFail();
        $this->cleanupRelationshipIds[] = $relationship->id;

        $leftModule = Module::withoutGlobalScopes()->where('slug', $relationship->left_module)->firstOrFail();
        $rightModule = Module::withoutGlobalScopes()->where('slug', $relationship->right_module)->firstOrFail();

        $leftRecord = $this->createRecordFor($leftModule);
        $rightRecord = $this->createRecordFor($rightModule);

        // Link initiated from the right-hand record's page, referencing the
        // left-hand record — matches how RelationshipManyToOneLinkingTest
        // and RelationshipManagerRouteTest exercise the link endpoint.
        $this->post("/modules/{$rightModule->slug}/{$rightRecord->id}/relationships/{$relationship->name}", [
            'related_ids' => [(string) $leftRecord->id],
        ])->assertSuccessful();

        $link = RelationshipLink::where('relationship_id', $relationship->id)->firstOrFail();
        $this->assertSame((string) $leftRecord->id, (string) $link->left_id);
        $this->assertSame((string) $rightRecord->id, (string) $link->right_id);

        $leftSide = RelationshipService::getWithSide($relationship->fresh(), $leftModule->slug);
        $rightSide = RelationshipService::getWithSide($relationship->fresh(), $rightModule->slug);

        [$expectedLeftRole, $expectedRightRole] = match ($relationship->type) {
            'one-to-one' => ['sibling', 'sibling'],
            'one-to-many' => ['parent', 'child'],
            'many-to-many' => ['parent', 'parent'],
        };
        $this->assertSame($expectedLeftRole, $leftSide->role);
        $this->assertSame($expectedRightRole, $rightSide->role);

        // Visible looking from the left record towards the right module...
        $relatedFromLeft = RelationshipService::getRelatedRecords($relationship->name, $leftModule->slug, (string) $leftRecord->id);
        $this->assertTrue($relatedFromLeft->pluck('id')->map(fn ($id) => (string) $id)->contains((string) $rightRecord->id));

        // ...and vice versa, from the right record towards the left module.
        $relatedFromRight = RelationshipService::getRelatedRecords($relationship->name, $rightModule->slug, (string) $rightRecord->id);
        $this->assertTrue($relatedFromRight->pluck('id')->map(fn ($id) => (string) $id)->contains((string) $leftRecord->id));

        // Unlink from the opposite side of where the link was created, to
        // also exercise the unlink endpoint both ways across the suite.
        $this->delete("/modules/{$leftModule->slug}/{$leftRecord->id}/relationships/{$relationship->name}/{$rightRecord->id}")
            ->assertSuccessful();
        $this->assertSame(0, RelationshipLink::where('relationship_id', $relationship->id)->count());

        return $relationship;
    }

    public function test_custom_module_relates_to_non_custom_module_across_all_types_both_ways(): void
    {
        $this->makeAdmin();

        $custom = $this->deployCustomModule('nc_'.Str::random(4));
        $accounts = $this->makeNonCustomAccountsModule();

        $this->assertTrue((bool) $custom->is_custom);
        $this->assertFalse((bool) $accounts->is_custom);

        // one-to-one: custom <-> accounts, siblings on both sides.
        $this->assertRelationshipWorksBothWays($custom, $accounts, 'one-to-one', 'cstm_nc_1to1_'.Str::random(6));

        // one-to-many: custom is the parent ("one"), accounts is the child ("many").
        $oneToMany = $this->assertRelationshipWorksBothWays($custom, $accounts, 'one-to-many', 'cstm_nc_1toM_'.Str::random(6));
        $this->assertSame($custom->slug, $oneToMany->left_module);
        $this->assertSame($accounts->slug, $oneToMany->right_module);

        // many-to-one, initiated from the SAME (custom) module's page: now
        // custom is the child ("many"), accounts is the parent ("one") — the
        // reverse cardinality of the case above, proving both directions of
        // the asymmetric type work from the custom module's perspective.
        $manyToOne = $this->assertRelationshipWorksBothWays($custom, $accounts, 'many-to-one', 'cstm_nc_Mto1_'.Str::random(6));
        $this->assertSame('one-to-many', $manyToOne->type);
        $this->assertSame($accounts->slug, $manyToOne->left_module);
        $this->assertSame($custom->slug, $manyToOne->right_module);

        // many-to-many: both sides are parents to each other.
        $this->assertRelationshipWorksBothWays($custom, $accounts, 'many-to-many', 'cstm_nc_MtoM_'.Str::random(6));
    }

    public function test_custom_module_relates_to_another_custom_module_across_all_types_both_ways(): void
    {
        $this->makeAdmin();

        $customA = $this->deployCustomModule('cc_a_'.Str::random(4));
        $customB = $this->deployCustomModule('cc_b_'.Str::random(4));

        $this->assertTrue((bool) $customA->is_custom);
        $this->assertTrue((bool) $customB->is_custom);

        $this->assertRelationshipWorksBothWays($customA, $customB, 'one-to-one', 'cstm_cstm_1to1_'.Str::random(6));

        $oneToMany = $this->assertRelationshipWorksBothWays($customA, $customB, 'one-to-many', 'cstm_cstm_1toM_'.Str::random(6));
        $this->assertSame($customA->slug, $oneToMany->left_module);
        $this->assertSame($customB->slug, $oneToMany->right_module);

        $manyToOne = $this->assertRelationshipWorksBothWays($customA, $customB, 'many-to-one', 'cstm_cstm_Mto1_'.Str::random(6));
        $this->assertSame('one-to-many', $manyToOne->type);
        $this->assertSame($customB->slug, $manyToOne->left_module);
        $this->assertSame($customA->slug, $manyToOne->right_module);

        $this->assertRelationshipWorksBothWays($customA, $customB, 'many-to-many', 'cstm_cstm_MtoM_'.Str::random(6));
    }
}
