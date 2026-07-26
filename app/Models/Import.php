<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Import extends Model
{
    use Prunable;

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

    /**
     * Abandoned uploads (never started) and finished-either-way imports both
     * become dead weight after a while. queued/processing rows are excluded
     * deliberately — a stuck-mid-flight import is a queue problem worth a
     * manual look, not something to silently delete.
     */
    public function prunable()
    {
        return static::whereIn('status', ['mapping', 'failed', 'completed'])
            ->where('updated_at', '<=', now()->subDays(Settings::get('retention_imports_days', 90)));
    }

    public function pruning()
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
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
