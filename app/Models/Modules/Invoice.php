<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BaseModule;
use Illuminate\Validation\ValidationException;

class Invoice extends BaseModule
{
  protected $fillable = [
    'name',
    'number',
    'status',
    'issue_date',
    'due_date',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total',
    'notes',
    'owner_id'
  ];

  protected $casts = [
    'issue_date'      => 'date',
    'due_date'        => 'date',
    'subtotal'        => 'decimal:2',
    'discount_amount' => 'decimal:2',
    'tax_amount'      => 'decimal:2',
    'total'           => 'decimal:2',
    'custom_fields'   => 'array',
  ];
       public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->number,
        ]);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $invoice) {
            if ($invoice->issue_date && $invoice->due_date && $invoice->issue_date->gt($invoice->due_date)) {
                throw ValidationException::withMessages([
                    'due_date' => 'Due date must be after issue date.',
                ]);
            }
        });
    }
}
