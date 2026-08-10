<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BaseModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

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
    'owner_id',
  ];

  protected $moduleCasts  = [
    'opened_at' => 'datetime',
    'closed_at' => 'datetime',

  ];
     public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->subject,
        ]);
    }
  protected static function booted(): void
  {
    parent::booted();

    static::saving(function ($case) {
      if ($case->isDirty('name')) {
        $case->subject = $case->name;
      }

      if ($case->opened_at && $case->closed_at && $case->opened_at->gt($case->closed_at)) {
        throw ValidationException::withMessages([
          'closed_at' => 'Closed date must be after opened date.',
        ]);
      }
    });
  }
}
