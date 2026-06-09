<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

return new class extends Migration
{
    private string $item = 'company-info';

    public function up(): void
    {
        $now = Carbon::now();

        $rows = [
            ['key' => 'company_name',    'label' => 'settings.fields.company_name',    'type' => 'string', 'sort_order' => 1, 'value' => ''],
            ['key' => 'company_address', 'label' => 'settings.fields.company_address', 'type' => 'string', 'sort_order' => 2, 'value' => ''],
            ['key' => 'company_phone',   'label' => 'settings.fields.company_phone',   'type' => 'string', 'sort_order' => 3, 'value' => ''],
            ['key' => 'company_email',   'label' => 'settings.fields.company_email',   'type' => 'string', 'sort_order' => 4, 'value' => ''],
            ['key' => 'company_website', 'label' => 'settings.fields.company_website', 'type' => 'string', 'sort_order' => 5, 'value' => ''],
            ['key' => 'company_logo_url','label' => 'settings.fields.company_logo_url','type' => 'string', 'sort_order' => 6, 'value' => ''],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('setting_values')->where('key', $row['key'])->exists();
            if ($exists) continue;

            DB::table('setting_values')->insert([
                'id'           => (string) Str::uuid(),
                'setting_item' => $this->item,
                'key'          => $row['key'],
                'value'        => $row['value'],
                'label'        => $row['label'],
                'type'         => $row['type'],
                'sort_order'   => $row['sort_order'],
                'autoload'     => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('setting_values')
            ->where('setting_item', $this->item)
            ->delete();
    }
};
