<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * This is an infrastructure class, a sibling class to Module/Field/Relationship.
 * Defines a reusable Action Conversion 
 * Reusable across records from the same module not across all modules. 
 * Each record of this class represents a definition and map for the action
 * 
 * I decided to label this action in the layout as Conversion after implementation
 * Transformations felt inaccurate as a description
 */
class Transformation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'conditions' => 'array',
        'enabled' => 'boolean',
        'automation_enabled' => 'boolean',
        'link_records_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $transformation) {
            if ($transformation->source_module === $transformation->target_module) {
                throw new \InvalidArgumentException('A transformation cannot target its own source module.');
            }
        });

        static::saved(function (self $transformation) {
            $transformation->ensureRelationship();
        });
    }

    /**
     * Evaluates this transformation's 'conditions' JSON against a record.
     * Only gates automatic execution, manual runs are always available whenever the transformation is enabled 
     * Conditions with no field are ignored
     * Two match modes only: all or any (full AND & full OR)
     * Groupping trees are not yet supported
     *
     * @param array<int, array{field: string, operator: string, value: mixed}> $conditions
     */
    public static function evaluateConditions(array $conditions, \App\Models\BaseModule $record, string $match = 'all'): bool
    {
        $conditions = array_filter($conditions, fn (array $c) => ! empty($c['field']));

        if (empty($conditions)) {
            return true;
        }

        $results = array_map(fn (array $c) => self::evaluateSingleCondition($c, $record), $conditions);

        return strtolower($match) === 'any'
            ? in_array(true, $results, true)
            : ! in_array(false, $results, true);
    }


    protected static function evaluateSingleCondition(array $condition, \App\Models\BaseModule $record): bool
    {
        $actual = $record->{$condition['field']} ?? null;
        $expected = $condition['value'] ?? null;

        return match ($condition['operator'] ?? 'equals') {
            'equals' => (string) $actual === (string) $expected,
            'not_equals' => (string) $actual !== (string) $expected,
            'contains' => str_contains((string) $actual, (string) $expected),
            'not_contains' => ! str_contains((string) $actual, (string) $expected),
            'starts_with' => str_starts_with((string) $actual, (string) $expected),
            'greater_than' => (float) $actual > (float) $expected,
            'less_than' => (float) $actual < (float) $expected,
            'before' => strtotime((string) $actual) < strtotime((string) $expected),
            'after' => strtotime((string) $actual) > strtotime((string) $expected),
            'between' => self::evaluateBetween($actual, $expected),
            'in' => in_array((string) $actual, array_map('strval', (array) $expected), true),
            'is_empty' => $actual === null || $actual === '',
            'is_not_empty' => $actual !== null && $actual !== '',
            default => false,
        };
    }

    protected static function evaluateBetween(mixed $actual, mixed $range): bool
    {
        [$min, $max] = array_pad((array) $range, 2, null);

        if ($min === null || $max === null) {
            return false;
        }

        if (is_numeric($min) && is_numeric($max)) {
            return (float) $actual >= (float) $min && (float) $actual <= (float) $max;
        }

        return strtotime((string) $actual) >= strtotime((string) $min)
            && strtotime((string) $actual) <= strtotime((string) $max);
    }

    public function passesConditions(\App\Models\BaseModule $record): bool
    {
        return self::evaluateConditions($this->conditions ?? [], $record, $this->conditions_match ?? 'all');
    }

    /**
     * @return HasMany<TransformationStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(TransformationStep::class)->orderBy('order');
    }

    /**
     * @return BelongsTo<Relationship, $this>
     */
    public function relationship(): BelongsTo
    {
        return $this->belongsTo(Relationship::class);
    }

    /**
     * Points this transformation at the Relationship row (if any) already defined between
     * its source and target module. Never creates one, relationships are only ever
     * created explicitly through Module Manager.
     * Does nothing when link_records_enabled is off
     */
    public function ensureRelationship(): ?Relationship
    {
        if (! $this->link_records_enabled) {
            return null;
        }

        $relationship = \App\Services\Relationships\RelationshipService::getRelationshipBetween(
            $this->source_module,
            $this->target_module,
        )->first();

        if ($relationship && $this->relationship_id !== $relationship->id) {
            $this->relationship_id = $relationship->id;
            $this->saveQuietly();
        }

        return $relationship;
    }

    /**
     * RelationshipService::link() silently replaces whichever record a
     * one-to-one relationship's two sides already point to. 
     * Returns the other-side record currently linked to $sourceRecord 
     */
    public function findConflictingLink(\App\Models\BaseModule $sourceRecord): ?array
    {
        if (! $this->link_records_enabled) {
            return null;
        }

        $relationship = $this->relationship;

        if (! $relationship || $relationship->type !== 'one-to-one') {
            return null;
        }

        $related = \App\Services\Relationships\RelationshipService::getRelatedRecords(
            $relationship->name,
            $this->source_module,
            $sourceRecord->id,
        )->first();

        if (! $related) {
            return null;
        }

        return [
            'id' => $related->id,
            'name' => $related->name ?? $related->number ?? $related->id,
        ];
    }
}
