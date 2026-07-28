<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransformationStep extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'configuration' => 'array',
        'order' => 'integer',
    ];

    /**
     * @return BelongsTo<Transformation, $this>
     */
    public function transformation(): BelongsTo
    {
        return $this->belongsTo(Transformation::class);
    }
}
