<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'account_id',
        'contact_id',
        'number',
        'status',
        'valid_until',
        'currency',
        'subtotal',
        'tax',
        'total',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
