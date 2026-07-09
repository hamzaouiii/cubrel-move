<?php

namespace Tests\Feature\Modules;

use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Modules\Deal;
use App\Models\Modules\Invoice;
use App\Models\Modules\Lead;
use App\Models\Modules\Order;
use App\Models\Modules\Product;
use App\Models\Modules\Quote;
use App\Models\Modules\SupportCase;
use App\Models\Settings\SettingValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\InteractsWithDashboardFixtures;
use Tests\TestCase;

class CustomFieldCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();

        
        
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);
    }

    

    public static function moduleProvider(): array
    {
        return [
            'leads'     => ['leads', Lead::class, true],
            'accounts'  => ['accounts', Account::class, true],
            'contacts'  => ['contacts', Contact::class, true],
            'deals'     => ['deals', Deal::class, true],
            'quotes'    => ['quotes', Quote::class, true],
            'orders'    => ['orders', Order::class, true],
            'invoices'  => ['invoices', Invoice::class, true],
            'products'  => ['products', Product::class, true],
            'cases'     => ['cases', SupportCase::class, true],
        ];
    }

    

    protected function setUpModule(string $slug, string $modelClass, bool $hasOwner): array
    {
        $module = $this->makeModule([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'path' => "/{$slug}",
            'has_owner' => $hasOwner,
            'model_class' => $modelClass,
            
            'table_name' => (new $modelClass)->getTable(),
        ]);

        $admin = $this->makeUser(['is_admin' => true, 'type' => 'admin']);
        $this->actingAs($admin);

        return [$module, $admin];
    }

    #[DataProvider('moduleProvider')]
    public function test_can_create_custom_field_definition(string $slug, string $modelClass, bool $hasOwner): void
    {
        [$module] = $this->setUpModule($slug, $modelClass, $hasOwner);

        $response = $this->post("/settings/modules/{$module->id}/fields/create", [
            'label' => 'Favourite Colour',
            'name' => 'favourite_colour',
            'key' => "{$slug}.favourite_colour",
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

    #[DataProvider('moduleProvider')]
    public function test_store_persists_custom_field_value(string $slug, string $modelClass, bool $hasOwner): void
    {
        [$module] = $this->setUpModule($slug, $modelClass, $hasOwner);
        $this->makeField($module, ['name' => 'favourite_colour', 'type' => 'text', 'is_custom' => true]);

        $response = $this->post("/{$slug}", [
            'name' => 'New Record',
            'favourite_colour' => 'Blue',
        ]);

        $response->assertRedirect();

        $record = $modelClass::where('name', 'New Record')->firstOrFail();
        $this->assertSame('Blue', $record->favourite_colour);
    }

    #[DataProvider('moduleProvider')]
    public function test_update_modifies_custom_field_value(string $slug, string $modelClass, bool $hasOwner): void
    {
        [$module] = $this->setUpModule($slug, $modelClass, $hasOwner);
        $this->makeField($module, ['name' => 'favourite_colour', 'type' => 'text', 'is_custom' => true]);
        $record = $modelClass::create(['name' => 'Original Name', 'favourite_colour' => 'Blue']);

        $response = $this->put("/{$slug}/{$record->id}", [
            'name' => 'Original Name',
            'favourite_colour' => 'Green',
        ]);

        $response->assertRedirect();

        $record->refresh();
        $this->assertSame('Green', $record->favourite_colour);
    }
}
