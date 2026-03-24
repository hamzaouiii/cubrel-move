import { computed } from "vue";

export const fieldTypeRules = {
  // Rules that apply to EVERY field type
  default: {
    hide: [
      "dropdown_list_id",
      "hidden",
      "filterable",
      "options",
      "default_value",
    ],
  },
  checkbox: {
    hide: ["required", "regex", "min_length", "max_length", "default_value"],
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
};

export function useFieldRules(form, metadata) {
  /**
   * Computed property to filter which fields should be visible in the form
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

    // Merge hide rules from default and specific type
    const hideList = [
      ...(defaultRules.hide || []),
      ...(specificRules.hide || []),
    ];

    return allFieldNames.filter((field) => !hideList.includes(field));
  });

  /**
   * Applies forced values (e.g., setting required to false for checkboxes)
   */
  const applyRules = (type) => {
    const defaultRules = fieldTypeRules.default?.set || {};
    const specificRules = fieldTypeRules[type]?.set || {};

    // Merge sets (specific overrides default)
    const sets = { ...defaultRules, ...specificRules };

    Object.entries(sets).forEach(([field, value]) => {
      if (field in form) {
        form[field] = value;
      }
    });
  };

  /**
   * Shared Helper: Identify checkbox types for the UI
   */
  const isCheckbox = (field) => {
    return [
      "readonly",
      "hidden",
      "required",
      "searchable",
      "filterable",
      "sortable",
    ].includes(field);
  };

  const isDropDown = (field) => field === "type";
  const isDisplayLabel = (field) => field === "label";
  const isRegex = (field) => field === "regex";

  /**
   * Shared Helper: Determine if a field should be disabled (Read Only)
   */
  const isReadonly = (field, isEditMode = false) => {
    // In edit mode, we usually don't allow changing the technical name or type
    if (isEditMode && (field === "name" || field === "type")) return true;
    // In create mode, only the name might be managed by a generator
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
    fieldTypeRules,
  };
}
