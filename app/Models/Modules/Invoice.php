<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'account_id',
        'contact_id',
        'quote_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'subtotal',
        'tax',
        'total',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date'   => 'date',
        'subtotal'   => 'decimal:2',
        'tax'        => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
