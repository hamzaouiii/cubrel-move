<?php

namespace App\Concerns;

use App\Services\Relationships\RelationshipService;

trait HasDynamicRelationships
{
  public function rel(string $relationshipName)
  {
    return RelationshipService::getRelatedRecords(
      $relationshipName,
      static::class,
      $this->id
    );
  }
}
