<script setup>
/**
 * Status Field Component - Enhanced Select with Color Highlighting
 *
 * Usage:
 * Send an array of options with optional color configurations:
 * options = [
 *   {
 *     value: 'active',
 *     label: 'Active',
 *     color: '#10b981',  // Custom color
 *     bgColor: '#d1fae5', // Custom background
 *     icon: 'fa-solid fa-check-circle' // Optional icon
 *   },
 *   { value: 'pending', label: 'Pending', status: 'warning' }, // Predefined style
 *   { value: 'inactive', label: 'Inactive', status: 'danger' }
 * ]
 *
 * Predefined status styles: 'success', 'warning', 'danger', 'info', 'default'
 */

import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  watch,
  getCurrentInstance,
} from "vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  modelValue: [String, Number, Boolean, Object, null],
  dropdown_list: Object,
  mode: {
    type: String,
    default: "edit",
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  searchPlaceholder: {
    type: String,
    default: "",
  },
  nullable: {
    type: Boolean,
    default: false,
  },
  highlight: String,
  // Status specific props
  showIcon: {
    type: Boolean,
    default: true,
  },
  pillStyle: {
    type: Boolean,
    default: true, // Use pill/badge style vs flat style
  },
});

const emit = defineEmits(["update:modelValue", "change"]);

// Predefined status color schemes
const statusStyles = {
  success: {
    color: "#065f46",
    bgColor: "#d1fae5",
    borderColor: "#a7f3d0",
    icon: "fa-solid fa-check-circle",
    lightBg: "#ecfdf5",
  },
  warning: {
    color: "#92400e",
    bgColor: "#fed7aa",
    borderColor: "#fed7aa",
    icon: "fa-solid fa-exclamation-triangle",
    lightBg: "#fffbeb",
  },
  danger: {
    color: "#991b1b",
    bgColor: "#fee2e2",
    borderColor: "#fecaca",
    icon: "fa-solid fa-times-circle",
    lightBg: "#fef2f2",
  },
  info: {
    color: "#1e40af",
    bgColor: "#bfdbfe",
    borderColor: "#bfdbfe",
    icon: "fa-solid fa-info-circle",
    lightBg: "#eff6ff",
  },
  default: {
    color: "#374151",
    bgColor: "#e5e7eb",
    borderColor: "#e5e7eb",
    icon: "fa-solid fa-circle",
    lightBg: "#f9fafb",
  },
};

const options = computed(() => {
  return props?.dropdown_list?.values || [];
});

const isOpen = ref(false);
const root = ref(null);
const search = ref("");
const searchInput = ref(null);

const normalizedOptions = computed(() => {
  let list = [];

  if (Array.isArray(options.value)) {
    list = options.value;
  } else if (options.value && typeof options.value === "object") {
    list = Object.values(options.value).flat();
  }

  // Process options to ensure they have status/style properties
  const processedList = list.map((option) => {
    // If option has predefined status string
    if (option.status && statusStyles[option.status]) {
      return {
        ...option,
        ...statusStyles[option.status],
      };
    }
    // If option has custom colors, use them
    if (option.color || option.bgColor) {
      return {
        ...statusStyles.default,
        ...option,
      };
    }
    // Default style
    return {
      ...statusStyles.default,
      ...option,
    };
  });

  if (props.nullable) {
    return processedList;
  }

  return [
    {
      value: null,
      label: "—",
      color: "#6b7280",
      bgColor: "#f3f4f6",
      icon: "fa-regular fa-circle",
    },
    ...processedList,
  ];
});

const selectedOption = computed(() => {
  const selected = normalizedOptions.value.find(
    (o) => o.value === props.modelValue,
  );
  if (selected && !selected.color) {
    return { ...statusStyles.default, ...selected };
  }
  return selected;
});

const filteredOptions = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!props.searchable || !q) return normalizedOptions.value;

  return normalizedOptions.value.filter((o) => {
    const label = String(o.label ?? "").toLowerCase();
    const desc = String(o.description ?? "").toLowerCase();
    const val = String(o.value ?? "").toLowerCase();
    return label.includes(q) || desc.includes(q) || val.includes(q);
  });
});

const getStatusStyle = (option) => {
  if (!option) return {};

  const style = {
    color: option.color || statusStyles.default.color,
    backgroundColor: option.bgColor || statusStyles.default.bgColor,
  };

  if (!props.pillStyle) {
    style.borderLeft = `3px solid ${option.color || statusStyles.default.color}`;
  }

  return style;
};

const toggle = async () => {
  if (props.disabled || props.readOnly) return;

  isOpen.value = !isOpen.value;

  if (isOpen.value) {
    await nextTick();
    if (props.searchable) {
      searchInput.value?.focus();
    }
  } else {
    search.value = "";
  }

  clearErrors();
};

const close = () => {
  isOpen.value = false;
  search.value = "";
};

const selectOption = (value) => {
  if (props.disabled || props.readOnly) return;

  if (value !== props.modelValue) {
    emit("update:modelValue", value);
    emit("change", value);
  }

  close();
};

const handleClickOutside = (event) => {
  if (!root.value) return;
  if (!root.value.contains(event.target)) {
    close();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});

const showError = ref(false);

watch(
  () => props.hasError,
  (val) => {
    showError.value = val;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};

const clearSelection = (e) => {
  e.stopPropagation();
  emit("update:modelValue", null);
  emit("change", null);
};

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};
</script>

<template>
  <div
    v-if="mode === 'edit' || mode === 'settings' || mode === 'module-builder'"
  >
    <div class="status-field" ref="root">
      <div
        class=""
        :class="{
          'status-field__control': !disabled && !readOnly,
          'is-open': isOpen,
          'is-invalid': showError,
          'is-disabled': disabled || readOnly,
        }"
        @click="toggle"
      >
        <div class="status-field__selected">
          <div
            v-if="selectedOption && selectedOption.value !== null"
            class="status-badge"
            :class="{
              'status-badge--pill': pillStyle,
              'status-badge--flat': !pillStyle,
            }"
            :style="getStatusStyle(selectedOption)"
          >
            <i
              v-if="showIcon && selectedOption.icon"
              :class="[selectedOption.icon, 'status-icon']"
              :style="{ color: selectedOption.color }"
            ></i>
            <span class="status-label">
              {{ t(selectedOption.label) }}
            </span>
          </div>
          <span v-else-if="disabled || readOnly">—</span>
          <span v-else class="placeholder">
            {{ t("settings.select") }}
          </span>
        </div>

        <span class="status-field__icons">
          <i
            v-if="showError"
            class="error-icon fa-solid fa-circle-exclamation"
          ></i>

          <i
            v-if="
              modelValue !== null && modelValue !== '' && !disabled && !readOnly
            "
            class="fa-solid fa-xmark status-field__clear"
            @click="clearSelection"
          ></i>

          <i
            v-if="!disabled && !readOnly"
            class="status-field__chevron fa-solid"
            :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
          ></i>
        </span>
      </div>

      <transition name="dropdown-fade">
        <div
          v-if="isOpen"
          class="status-field__menu"
          role="listbox"
          @click.stop
        >
          <div v-if="searchable" class="status-field__search-wrapper">
            <input
              ref="searchInput"
              v-model="search"
              type="text"
              class="status-field__search-input"
              :placeholder="t('settings.search_in_drop_down')"
              @keydown.stop
            />
          </div>

          <ul class="status-field__list">
            <li
              v-for="option in filteredOptions"
              :key="option.value"
              class="status-field__option"
              :class="{ 'is-active': option.value === modelValue }"
              role="option"
              @click="selectOption(option.value)"
            >
              <div
                class="status-option-preview"
                :style="{
                  backgroundColor:
                    option.bgColor || statusStyles.default.bgColor,
                  borderLeftColor: option.color || statusStyles.default.color,
                }"
              >
                <div
                  class="status-option-badge"
                  :class="{ 'status-option-badge--pill': pillStyle }"
                  :style="{
                    color: option.color || statusStyles.default.color,
                    backgroundColor:
                      option.bgColor || statusStyles.default.bgColor,
                  }"
                >
                  <i
                    v-if="showIcon && option.icon"
                    :class="[option.icon, 'status-option-icon']"
                  ></i>
                  <span>{{ t(option.label) }}</span>
                </div>
                <div
                  v-if="option.description"
                  class="status-field__option-description"
                >
                  {{ option.description }}
                </div>
              </div>
            </li>

            <li
              v-if="filteredOptions.length === 0"
              class="status-field__no-results"
            >
              {{ t("settings.dropdown_no_results") }}
            </li>
          </ul>
        </div>
      </transition>
    </div>
  </div>

  <div v-else-if="mode === 'dashboard'" class="df-select" ref="root">
    <div class="df-select__control" :class="{ 'is-open': isOpen }" @click="toggle">
      <span class="df-select__value">
        <div v-if="selectedOption && selectedOption.value !== null" class="status-badge status-badge--pill" :style="getStatusStyle(selectedOption)">
          <i v-if="showIcon && selectedOption.icon" :class="[selectedOption.icon, 'status-icon']" :style="{ color: selectedOption.color }"></i>
          <span class="status-label">{{ t(selectedOption.label) }}</span>
        </div>
        <span v-else class="df-select__placeholder">{{ t('settings.select') }}</span>
      </span>
      <span class="df-select__icons">
        <i v-if="modelValue !== null && modelValue !== ''" class="fa-solid fa-xmark df-select__clear" @click.stop="clearSelection"></i>
        <i class="fa-solid" :class="isOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
      </span>
    </div>
    <transition name="dropdown-fade">
      <div v-if="isOpen" class="status-field__menu" role="listbox" @click.stop>
        <div v-if="searchable" class="status-field__search-wrapper">
          <input ref="searchInput" v-model="search" type="text" class="status-field__search-input" :placeholder="t('settings.search_in_drop_down')" @keydown.stop />
        </div>
        <ul class="status-field__list">
          <li v-for="option in filteredOptions" :key="option.value" class="status-field__option" :class="{ 'is-active': option.value === modelValue }" role="option" @click="selectOption(option.value)">
            <div class="status-option-preview" :style="{ backgroundColor: option.bgColor || statusStyles.default.bgColor, borderLeftColor: option.color || statusStyles.default.color }">
              <div class="status-option-badge status-option-badge--pill" :style="{ color: option.color || statusStyles.default.color, backgroundColor: option.bgColor || statusStyles.default.bgColor }">
                <i v-if="showIcon && option.icon" :class="[option.icon, 'status-option-icon']"></i>
                <span>{{ t(option.label) }}</span>
              </div>
            </div>
          </li>
          <li v-if="filteredOptions.length === 0" class="status-field__no-results">{{ t('settings.dropdown_no_results') }}</li>
        </ul>
      </div>
    </transition>
  </div>

  <div v-else-if="mode === 'detail'">
    <div class="status-field status-field--detail display-field">
      <i class="status-detail-icon fa-solid fa-tag"></i>

      <div
        class="status-detail-content"
        v-if="selectedOption && selectedOption.value !== null"
      >
        <div
          class="status-badge"
          :class="{
            'status-badge--pill': pillStyle,
            'status-badge--flat': !pillStyle,
            'status-badge--clickable': !readOnly,
          }"
          :style="getStatusStyle(selectedOption)"
          @click="!readOnly && toggle()"
        >
          <i
            v-if="showIcon && selectedOption.icon"
            :class="[selectedOption.icon, 'status-icon']"
            :style="{ color: selectedOption.color }"
          ></i>
          <span class="status-label">
            {{ t(selectedOption.label) }}
          </span>
        </div>
      </div>

      <div v-else class="status-empty">
        <span>—</span>
      </div>
    </div>
  </div>

  <div v-else-if="mode === 'table'">
    <div class="status-field status-field--table">
      <div
        v-if="selectedOption && selectedOption.value !== null"
        class="status-badge status-badge"
        :class="{ 'status-badge--pill': pillStyle }"
        :style="getStatusStyle(selectedOption)"
      >
        <i
          v-if="showIcon && selectedOption.icon"
          :class="[selectedOption.icon, 'status-icon']"
        ></i>
        <span
          v-if="searchable"
          v-html="highlightMatch(t(selectedOption.label))"
        ></span>
        <span v-else>{{ t(selectedOption.label) }}</span>
      </div>
      <span v-else class="field-empty">—</span>
    </div>
  </div>

  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    <div class="status-field status-field--related">
      <div
        v-if="selectedOption && selectedOption.value !== null"
        class="status-badge"
        :class="{ 'status-badge--pill': pillStyle }"
        :style="getStatusStyle(selectedOption)"
      >
        <i
          v-if="showIcon && selectedOption.icon"
          :class="[selectedOption.icon, 'status-icon']"
        ></i>
        <span>{{ t(selectedOption.label) }}</span>
      </div>
      <span v-else class="field-empty">—</span>
    </div>
  </div>

  <div v-else-if="mode === 'profile-header'">
    <div
      v-if="selectedOption && selectedOption.value !== null"
      class="status-badge status-badge--profile"
      :class="{ 'status-badge--pill': pillStyle }"
      :style="getStatusStyle(selectedOption)"
    >
      <i
        v-if="showIcon && selectedOption.icon"
        :class="[selectedOption.icon, 'status-icon']"
      ></i>
      <span>{{ t(selectedOption.label) }}</span>
    </div>
    <span v-else>—</span>
  </div>
</template>
