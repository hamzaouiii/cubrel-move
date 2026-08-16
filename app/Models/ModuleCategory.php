<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'label',
        'sort_order',
    ];

    /**
     * @return HasMany<Module, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public static function nextSortOrder(): int
    {
        return (self::max('sort_order') ?? 0) + 1;
    }


    public static function asSelectOptions(): array
    {
        return [
            'values' => self::orderBy('sort_order')
                ->get(['id', 'label'])
                ->map(fn (ModuleCategory $category) => [
                    'label' => $category->label,
                    'value' => $category->id,
                ])
                ->all(),
        ];
    }
}
