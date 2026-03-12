<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BaseModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupportCase extends BaseModule

{
  use HasFactory;
  protected $table = 'cases';

  protected $fillable = [
    'name',
    'subject',
    'description',
    'status',
    'priority',
    'opened_at',
    'closed_at',
  ];

  protected $casts = [
    'opened_at' => 'datetime',
    'closed_at' => 'datetime',
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class);
  }

  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class);
  }
  protected static function booted()
  {
    static::saving(function ($case) {
      if ($case->isDirty('name')) {
        $case->subject = $case->name;
      }
    });
  }
}
