<?php

namespace Database\Seeders;

use App\Models\Transformation;
use App\Models\TransformationStep;
use Illuminate\Database\Seeder;

/**
 * Seeds the Quote -> Invoice transformation as the engine's reference
 * implementation. Kept as ordinary rows (not migration-baked) so it
 * stays fully editable via the Studio Transformations builder
 * afterward, same as any transformation an admin creates by hand.
 *
 * Idempotent against migrate:fresh --seed: transformation + its steps
 * are looked up by a stable key (source/target module pair) and
 * replaced wholesale rather than duplicated on re-seed.
 */
class TransformationSeeder extends Seeder
{
    public function run(): void
    {
        $transformation = Transformation::updateOrCreate(
            ['source_module' => 'quotes', 'target_module' => 'invoices'],
            [
                'name' => 'Invoice',
                'description' => 'Create an Invoice from an accepted Quote.',
                'enabled' => true,
                'manual_enabled' => true,
                'automation_enabled' => false,
                'conditions' => [
                    ['field' => 'status', 'operator' => '==', 'value' => 'accepted'],
                    ['field' => 'total', 'operator' => '>', 'value' => 0],
                ],
            ]
        );

        // Model events are suppressed during seeding (DatabaseSeeder uses
        // WithoutModelEvents), so the relationship auto-provisioning that
        // normally runs on Transformation::saved() must be called explicitly.
        $transformation->ensureRelationship();

        $transformation->steps()->delete();

        $transformation->steps()->createMany([
            ['order' => 0, 'type' => 'create_record', 'configuration' => []],
            ['order' => 1, 'type' => 'copy_fields', 'configuration' => [
                'mappings' => [
                    ['mode' => 'field', 'source_field' => 'name', 'target_field' => 'name'],
                    ['mode' => 'field', 'source_field' => 'description', 'target_field' => 'description'],
                    ['mode' => 'field', 'source_field' => 'notes', 'target_field' => 'notes'],
                    ['mode' => 'field', 'source_field' => 'owner_id', 'target_field' => 'owner_id'],
                    // Target fields with no direct source counterpart are
                    // just mapping rows in static/expression mode — there is
                    // no separate "defaults" list.
                    ['mode' => 'static', 'target_field' => 'status', 'value' => 'draft'],
                    ['mode' => 'expression', 'target_field' => 'issue_date', 'expression' => [
                        ['type' => 'helper', 'value' => 'today'],
                    ]],
                ],
            ]],
            ['order' => 2, 'type' => 'copy_relationships', 'configuration' => [
                'relationships' => ['line_items', 'notes'],
            ]],
            ['order' => 3, 'type' => 'link_records', 'configuration' => []],
        ]);
    }
}
