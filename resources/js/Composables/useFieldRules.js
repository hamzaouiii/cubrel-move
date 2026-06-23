import { computed } from "vue";

export const fieldTypeRules = {
  // Rules that apply to EVERY field type
  default: {
    hide: [
      "dropdown_list_id",
      "hidden",
      "options",
      "default_value",
      "related_module",
      "min_length",
      "max_length",
    ],
  },
  checkbox: {
    hide: ["required", "regex", "default_value"],
    set: { required: false },
  },
  select: {
    hide: ["regex", "min_length", "max_length"],
  },
  date: {
    hide: ["regex", "min_length", "max_length"],
  },
  datetime: {
    hide: ["regex", "min_length", "max_length"],
  },
  record: {
    // Explicitly surfaces fields hidden by default
    show: ["related_module"],
  },
};

export function useFieldRules(form, metadata) {
  /**
   * Computed property to filter which fields should be visible in the form.
   *
   * Resolution order (highest priority first):
   *  1. type-level `show`  → always visible, overrides any hide rule
   *  2. type-level `hide`  → hidden for this specific type
   *  3. default-level `hide` → hidden for all types unless overridden by (1)
   */
  const visibleMetadata = computed(() => {
    const data = metadata.value;
    if (!data) return [];

    // Normalize keys: Create (Array values) vs Edit (Object keys)
    const keys = Object.keys(data);
    const isNumericIndexed = keys.length > 0 && !isNaN(keys[0]);
    const allFieldNames = isNumericIndexed ? Object.values(data) : keys;

    const type = form.type;
    const defaultRules = fieldTypeRules.default || {};
    const specificRules = fieldTypeRules[type] || {};

    // Fields that are force-shown override any hide rule
    const showList = new Set(specificRules.show || []);

    // Merge hide lists; type-level additions stack on top of defaults
    const hideList = new Set([
      ...(defaultRules.hide || []),
      ...(specificRules.hide || []),
    ]);

    return allFieldNames.filter((field) => {
      // show rule wins over everything
      if (showList.has(field)) return true;
      // otherwise respect the merged hide list
      return !hideList.has(field);
    });
  });

  /**
   * Applies forced values (e.g., setting required to false for checkboxes).
   */
  const applyRules = (type) => {
    const defaultSets = fieldTypeRules.default?.set || {};
    const specificSets = fieldTypeRules[type]?.set || {};
    // Specific overrides default
    const sets = { ...defaultSets, ...specificSets };
    Object.entries(sets).forEach(([field, value]) => {
      if (field in form) {
        form[field] = value;
      }
    });
  };

  /** Identify checkbox-style boolean fields for the UI */
  const isCheckbox = (field) =>
    [
      "readonly",
      "hidden",
      "required",
      "searchable",
      "filterable",
      "sortable",
    ].includes(field);

  const isDropDown = (field) => field === "type";
  const isDisplayLabel = (field) => field === "label";
  const isRegex = (field) => field === "regex";
  const isRelatedModule = (field) => field === "related_module";

  /**
   * Determine if a field should be read-only.
   * In edit mode: name + type are locked.
   * In create mode: name is managed by the generator.
   */
  const isReadonly = (field, isEditMode = false) => {
    if (isEditMode && (field === "name" || field === "type")) return true;
    return field === "name";
  };

  return {
    visibleMetadata,
    applyRules,
    isCheckbox,
    isDropDown,
    isReadonly,
    isDisplayLabel,
    isRegex,
    isRelatedModule,
    fieldTypeRules,
  };
}
