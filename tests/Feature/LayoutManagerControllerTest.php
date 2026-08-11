<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class LayoutManagerControllerTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();
    }

    protected function storeRecordLayout(Module $module, array $sections)
    {
        $admin = $this->makeUser(['is_admin' => true]);

        return $this->actingAs($admin)->post(
            route('settings.modules.layouts.store', [$module->id, 'record']),
            ['definition' => ['sections' => $sections]]
        );
    }

    public function test_record_layout_missing_a_required_field_is_rejected(): void
    {
        $module = $this->makeModule();
        $this->makeField($module, ['name' => 'reported_by', 'label' => 'Reported By', 'required' => true]);
        $this->makeField($module, ['name' => 'description', 'required' => false]);

        $response = $this->storeRecordLayout($module, [
            [
                'name' => 'Card',
                'layout' => [
                    ['name' => 'description', 'type' => 'text', 'label' => 'Description'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('definition.sections');
        $this->assertStringContainsString(
            'Reported By',
            session('errors')->get('definition.sections')[0]
        );
        $this->assertDatabaseMissing('layouts', ['module_id' => $module->id, 'type' => 'record']);
    }

    public function test_required_field_error_resolves_db_backed_custom_label(): void
    {
        $module = $this->makeModule();
        $this->makeField($module, [
            'name' => 'reported_by',
            'label' => 'modules.contacts.fields.reported_by',
            'required' => true,
        ]);
        $this->makeField($module, ['name' => 'description', 'required' => false]);

        Label::create([
            'key' => 'modules.contacts.fields.reported_by',
            'value' => 'Reported By',
        ]);

        $response = $this->storeRecordLayout($module, [
            [
                'name' => 'Card',
                'layout' => [
                    ['name' => 'description', 'type' => 'text', 'label' => 'Description'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('definition.sections');
        $message = session('errors')->get('definition.sections')[0];
        $this->assertStringContainsString('Reported By', $message);
        $this->assertStringNotContainsString('modules.contacts.fields.reported_by', $message);
    }

    public function test_record_layout_with_all_required_fields_is_saved(): void
    {
        $module = $this->makeModule();
        $this->makeField($module, ['name' => 'name', 'required' => true]);
        $this->makeField($module, ['name' => 'description', 'required' => false]);

        $response = $this->storeRecordLayout($module, [
            [
                'name' => 'Card',
                'layout' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name'],
                    ['name' => 'description', 'type' => 'text', 'label' => 'Description'],
                ],
            ],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('layouts', ['module_id' => $module->id, 'type' => 'record']);
    }

    public function test_required_readonly_fields_are_exempt(): void
    {
        $module = $this->makeModule();
        $this->makeField($module, ['name' => 'name', 'required' => true]);
        $this->makeField($module, ['name' => 'created_by', 'required' => true, 'readonly' => true]);

        $response = $this->storeRecordLayout($module, [
            [
                'name' => 'Card',
                'layout' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Name'],
                ],
            ],
        ]);

        $response->assertSessionDoesntHaveErrors();
    }
}
