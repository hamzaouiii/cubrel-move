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

/**
 * Custom fields aren't declared on the module itself — they're rows in the
 * `fields` table (is_custom = true) created via FieldsManagerController, and
 * their values live in each record's `custom_fields` JSON column, handled
 * transparently by the HasCustomFields trait. This covers the full lifecycle:
 * defining a custom field, storing a value through it, and updating it.
 */
class CustomFieldCrudTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDashboardFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->completeOnboarding();

        // Same pagination-setting gap as ModuleCrudTest; unused here but kept
        // for parity in case a future test in this class touches the index page.
        SettingValue::create(['setting_item' => 'preferences', 'key' => 'list_view_limit', 'value' => 25]);
    }

    /**
     * 'inquiries' is deliberately excluded: its backing table (contact_messages)
     * has no custom_fields column at all. add_json_to_all_module_tables lists
     * 'inquiries' as a table name, but no such table exists (it's
     * 'contact_messages'), so that migration silently no-ops for this module —
     * custom fields don't work for Inquiries in the app today either.
     */
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

    /**
     * Registers the module and returns [Module, admin User]. Field definitions
     * are managed under /settings/..., which sits behind AdminMiddleware.
     */
    protected function setUpModule(string $slug, string $modelClass, bool $hasOwner): array
    {
        $module = $this->makeModule([
            'slug' => $slug,
            'name' => ucfirst($slug),
            'path' => "/{$slug}",
            'has_owner' => $hasOwner,
            'model_class' => $modelClass,
            // HasCustomFields looks up the module by table_name, which for
            // 'inquiries' differs from the slug (backed by 'contact_messages').
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
