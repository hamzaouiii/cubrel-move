<?php

namespace App\Concerns;

use App\Models\Field;
use App\Models\Module;

trait HasCustomFields
{
  protected static array $customFieldCache = [];
  protected static array $moduleCache = [];
  public function __set($key, $value)
  {
    if (
      array_key_exists($key, $this->attributes)
      || array_key_exists($key, $this->casts)
      || $this->hasGetMutator($key)
    ) {
      return parent::setAttribute($key, $value);
    }

    if ($this->isCustomField($key)) {
      return $this->getCustomFieldValue($key);
    }

    return parent::__set($key, $value);
  }

  public function __get($key)
  {
    if (
      array_key_exists($key, $this->attributes)
      || array_key_exists($key, $this->casts)
      || $this->hasGetMutator($key)
    ) {
      return parent::getAttribute($key);
    }
    if ($this->isCustomField($key)) { // line 38
      return $this->getAttribute($key);
    }

    return parent::__get($key); // 42
  }

  public function getAttribute($key)
  {
    if ($key == 'custom_fields') {
      return $this->getAllCustomFields();
    }
    if ($this->hasGetMutator($key) || $this->attributeExists($key)) {
      return parent::getAttribute($key);
    }
    if ($this->isCustomField($key)) { //53
      return $this->getCustomFieldValue($key);
    }

    return parent::getAttribute($key);
  }

  public function setAttribute($key, $value)
  {
    if ($this->isCustomField($key)) {
      $custom = $this->getCustomFieldsArray();
      $custom[$key] = $value;
      $this->attributes['custom_fields'] = json_encode($custom);
      return $this;
    }

    return parent::setAttribute($key, $value);
  }

  public function fill(array $attributes)
  {
    $customFields = [];
    $regularAttributes = [];

    foreach ($attributes as $key => $value) {
      if ($this->isCustomField($key)) {
        $customFields[$key] = $value;
      } else {
        $regularAttributes[$key] = $value;
      }
    }

    parent::fill($regularAttributes);

    if (!empty($customFields)) {
      $currentCustom = $this->getCustomFieldsArray();
      $mergedCustom = array_merge($currentCustom, $customFields);
      $this->attributes['custom_fields'] = json_encode($mergedCustom);
    }

    return $this;
  }

  public function toArray()
  {
    $attributes = parent::toArray();

    $customFields = $this->getCachedCustomFields();
    foreach ($customFields as $field) {
      $attributes[$field] = $this->getCustomFieldValue($field);
    }

    return $attributes;
  }

  protected function isCustomField(string $key): bool
  {
    return in_array($key, $this->getCachedCustomFields(), true);
  }

  protected function getCustomFieldValue(string $key) : string|array|null
  {
    $custom = $this->getCustomFieldsArray();
    return $custom[$key] ?? null;
  }

  protected function getAllCustomFields(): array
  {
    $result = [];
    $fields = $this->getCachedCustomFields();
    $custom = $this->getCustomFieldsArray();

    foreach ($fields as $field) {
      $result[$field] = $custom[$field] ?? null;
    }

    return $result;
  }

  protected function getCustomFieldsArray(): array
  {
    $custom = $this->attributes['custom_fields'] ?? null;
    if ($custom && is_string($custom)) {
      return json_decode($custom, true) ?: [];
    }
    return [];
  }

  protected function getCachedCustomFields(): array
  {
    $table = $this->getTable();
    if (!isset(self::$moduleCache[$table])) {
      self::$moduleCache[$table] = Module::query()
        ->where('table_name', $table)
        ->where('is_active', 1)
        ->first(); //148
    }

    $module = self::$moduleCache[$table];


    if (!$module) {
      return [];
    }

    $moduleId = $module->id;

    if (!isset(self::$customFieldCache[$moduleId])) {
      self::$customFieldCache[$moduleId] = Field::query()
        ->where('module_id', $moduleId)
        ->where('is_custom', 1)
        ->pluck('name')
        ->all();
    }

    return self::$customFieldCache[$moduleId];
  }

  protected function attributeExists(string $key): bool
  {
    return array_key_exists($key, $this->attributes)
      || array_key_exists($key, $this->casts);
  }
}
