<?php

namespace Tests\Feature;

use App\Models\DropdownList;
use Database\Seeders\dropdownListSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * config/default_relationship_types.php is the single source of truth for which
 * relationship types exist — config/dropdown_lists.php no longer carries its own
 * separate 'relationship_type_list' entry. This covers dropdownListSeeder building
 * that dropdown's options from the config at seed time, rather than the two lists
 * drifting out of sync (see incomplete-features.md, resolved item).
 */
class DropdownListSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationship_type_list_is_seeded_from_the_relationship_types_config(): void
    {
        config(['default_relationship_types' => ['one-to-one', 'one-to-many', 'many-to-one', 'many-to-many']]);

        (new dropdownListSeeder())->run();

        $dropdown = DropdownList::where('key', 'relationship_type_list')->firstOrFail();

        $this->assertSame(
            ['one-to-one', 'one-to-many', 'many-to-one', 'many-to-many'],
            collect($dropdown->values)->pluck('value')->all(),
        );
        $this->assertSame('relationships.types.many-to-one', collect($dropdown->values)->firstWhere('value', 'many-to-one')['label']);
    }

    public function test_relationship_type_list_reflects_config_changes(): void
    {
        config(['default_relationship_types' => ['one-to-one']]);

        (new dropdownListSeeder())->run();

        $dropdown = DropdownList::where('key', 'relationship_type_list')->firstOrFail();

        $this->assertSame(['one-to-one'], collect($dropdown->values)->pluck('value')->all());
    }

    public function test_config_dropdown_lists_no_longer_defines_relationship_type_list(): void
    {
        $this->assertArrayNotHasKey('relationship_type_list', config('dropdown_lists', []));
    }
}
