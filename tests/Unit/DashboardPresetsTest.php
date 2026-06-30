<?php

namespace Tests\Unit;

use App\Support\DashboardPresets;
use Tests\TestCase;

class DashboardPresetsTest extends TestCase
{
    protected function user(?string $type, bool $isAdmin): object
    {
        return new class($type, $isAdmin)
        {
            public function __construct(public ?string $type, private bool $isAdmin) {}

            public function isAdmin(): bool
            {
                return $this->isAdmin;
            }
        };
    }

    public function test_preset_type_returns_matching_type_when_defined(): void
    {
        $this->assertSame('sales_rep', DashboardPresets::presetType($this->user('sales_rep', false)));
    }

    public function test_preset_type_falls_back_to_admin_for_admin_flagged_user_with_unknown_type(): void
    {
        $this->assertSame('admin', DashboardPresets::presetType($this->user('some_unmapped_type', true)));
    }

    public function test_preset_type_falls_back_to_read_only_for_non_admin_unknown_type(): void
    {
        $this->assertSame('read_only', DashboardPresets::presetType($this->user('some_unmapped_type', false)));
    }

    public function test_layout_stamps_fresh_instance_ids_on_object_items(): void
    {
        $layout = DashboardPresets::layout('sales_rep');
        $objectItems = array_filter($layout, fn ($item) => is_array($item));

        $this->assertNotEmpty($objectItems);
        foreach ($objectItems as $item) {
            $this->assertArrayHasKey('instanceId', $item);
            $this->assertNotEmpty($item['instanceId']);
        }
    }

    public function test_layout_preserves_string_items_unchanged(): void
    {
        $layout = DashboardPresets::layout('sales_rep');

        $this->assertContains('my-records', $layout);
    }

    public function test_layout_falls_back_to_read_only_preset_for_unknown_type(): void
    {
        $fallback = DashboardPresets::layout('read_only');
        $unknown  = DashboardPresets::layout('totally-made-up-type');

        $this->assertCount(count($fallback), $unknown);
    }

    public function test_layout_returns_unique_instance_ids_across_widgets(): void
    {
        $layout = DashboardPresets::layout('admin');
        $ids = array_column(array_filter($layout, fn ($item) => is_array($item)), 'instanceId');

        $this->assertSame(count($ids), count(array_unique($ids)));
    }
}
