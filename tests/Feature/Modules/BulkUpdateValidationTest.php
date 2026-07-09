<?php

namespace Tests\Feature\Modules;

use App\Handlers\Modules\AccountsModuleHandler;
use App\Models\Modules\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class BulkUpdateValidationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->completeOnboarding();

        $module = $this->makeModule([
            'slug' => 'accounts',
            'name' => 'Accounts',
            'path' => '/accounts',
            'model_class' => Account::class,
            'handler_class' => AccountsModuleHandler::class,
            'table_name' => 'accounts',
        ]);

        $this->makeField($module, [
            'name' => 'name',
            'key' => 'accounts.name',
            'type' => 'text',
            'required' => true,
        ]);

        $this->makeField($module, [
            'name' => 'website',
            'key' => 'accounts.website',
            'type' => 'text',
            'required' => false,
        ]);

        $this->actingAs($this->makeUser(['is_admin' => true]));
    }

    public function test_explicit_selection_rejects_clearing_a_required_field(): void
    {
        $account = Account::create(['name' => 'Original Name']);

        $this->put('/accounts', [
            'field' => 'accounts.name',
            'value' => '',
            'selectedIds' => [(string) $account->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('error');

        $this->assertSame('Original Name', $account->fresh()->name);
    }

    public function test_all_matching_rejects_clearing_a_required_field(): void
    {
        $account = Account::create(['name' => 'Original Name']);

        $this->put('/accounts', [
            'field' => 'accounts.name',
            'value' => null,
            'allMatchingSelected' => true,
            'filters' => [],
        ])->assertSessionHas('error');

        $this->assertSame('Original Name', $account->fresh()->name);
    }

    public function test_setting_a_required_field_to_a_real_value_succeeds(): void
    {
        $account = Account::create(['name' => 'Original Name']);

        $this->put('/accounts', [
            'field' => 'accounts.name',
            'value' => 'New Name',
            'selectedIds' => [(string) $account->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $this->assertSame('New Name', $account->fresh()->name);
    }

    public function test_clearing_a_non_required_field_still_succeeds(): void
    {
        $account = Account::create(['name' => 'Original Name', 'website' => 'https://example.com']);

        $this->put('/accounts', [
            'field' => 'accounts.website',
            'value' => '',
            'selectedIds' => [(string) $account->id],
            'allMatchingSelected' => false,
        ])->assertSessionHas('success');

        $this->assertEmpty($account->fresh()->website);
    }
}
