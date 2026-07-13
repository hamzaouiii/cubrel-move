<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Import extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'column_mapping' => 'array',
        'errors' => 'array',
        'errors_truncated' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressPercent(): int
    {
        if (empty($this->total_rows)) {
            return 0;
        }

        return (int) round(min($this->processed_rows, $this->total_rows) / $this->total_rows * 100);
    }

    /**
     * @param  Collection<int, Field>  $fields
     * @return Collection<int, Field>
     */
    public static function mappableFields(Collection $fields): Collection
    {
        // readonly / calculated fields are never writable
        //  excluded_fields are types with no text-based mapping strategy (config/import.php)
        return $fields->filter(fn (Field $field) => ! $field->readonly
            && ! $field->is_calculated
            && ! in_array($field->type, config('import.excluded_fields'), true)
        )->values();
    }
}
