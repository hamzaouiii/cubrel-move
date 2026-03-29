<script setup>
import { computed, ref, watch } from "vue";
import { useClipboard } from "@/Composables/useClipboard";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: "",
  },
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
  highlight: String,
  searchable: Boolean,
  min: {
    type: Number,
    default: null,
  },
  max: {
    type: Number,
    default: null,
  },
  step: {
    type: Number,
    default: 0.01,
  },
  precision: {
    type: Number,
    default: 2,
  },
  placeholder: {
    type: String,
  },
});

const localValue = computed({
  get: () => {
    if (props.modelValue === null || props.modelValue === "") return "";
    const num = parseFloat(props.modelValue);
    return isNaN(num) ? "" : num;
  },
  set: (val) => {
    if (val === "" || val === null) {
      emit("update:modelValue", null);
    } else {
      let num = parseFloat(val);
      if (!isNaN(num)) {
        // Round to specified precision
        num = Number(num.toFixed(props.precision));
        // Apply min/max constraints if provided
        if (props.min !== null && num < props.min) num = props.min;
        if (props.max !== null && num > props.max) num = props.max;
        emit("update:modelValue", num);
      } else {
        emit("update:modelValue", val);
      }
    }
  },
});

const showError = ref(false);
const { copied, copy } = useClipboard();

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

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text && text !== 0) return "-";
  if (!props.highlight || !props.highlight.trim())
    return text?.toString() || "-";

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};

// Format decimal with thousand separators and fixed precision
const formatDecimal = (value) => {
  if (value === null || value === "" || isNaN(parseFloat(value))) return "—";
  const num = parseFloat(value);
  return num.toLocaleString(undefined, {
    minimumFractionDigits: props.precision,
    maximumFractionDigits: props.precision,
  });
};

// Handle input change
const handleInputChange = (event) => {
  let value = event.target.value;
  // Allow digits, decimal point, and minus sign
  value = value.replace(/[^\d.-]/g, "");
  // Ensure only one decimal point
  const parts = value.split(".");
  if (parts.length > 2) {
    value = parts[0] + "." + parts.slice(1).join("");
  }
  localValue.value = value;
};
</script>

<template>
  <!-- Edit Mode -->
  <div v-if="mode === 'edit'">
    <span
      class="decimal-field decimal-field--edit"
      :class="{
        'decimal-field--error': showError,
        'decimal-field--readonly': readOnly,
      }"
    >
      <div class="decimal-input-wrapper">
        <span v-if="readOnly">{{ formatDecimal(localValue) }}</span>
        <input
          v-else
          v-model="localValue"
          @input="
            clearErrors();
            handleInputChange($event);
          "
          type="number"
          :min="min"
          :max="max"
          :step="step"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <!-- Detail Mode -->
  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'decimal-field decimal-field--detail display-field',
        { 'decimal-field--readonly': readOnly },
      ]"
    >
      <div
        class="decimal-detail-content"
        v-if="modelValue !== null && modelValue !== ''"
      >
        <span class="decimal-value">{{ formatDecimal(modelValue) }}</span>
      </div>
      <div v-else>—</div>
      <button
        v-if="modelValue !== null && modelValue !== '' && !readOnly"
        class="copy-btn"
        @click.stop.prevent="copy(modelValue)"
        :title="copied ? 'Copied!' : 'Copy value'"
      >
        <i
          :class="
            copied ? 'fa-solid fa-check text-success' : 'fa-regular fa-copy'
          "
        ></i>
      </button>
    </div>
  </div>

  <!-- Table/Related Panel/Linking Panel Mode -->
  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <div class="decimal-field decimal-field--table">
      <span v-if="searchable">
        <span v-html="highlightMatch(formatDecimal(modelValue))"></span>
      </span>
      <span v-else class="decimal-table-text">
        {{ formatDecimal(modelValue) }}
      </span>
    </div>
  </div>

  <!-- Settings Mode -->
  <div v-if="mode === 'settings'">
    <span
      class="decimal-field decimal-field--edit decimal-field--settings"
      :class="{
        'decimal-field--error': showError,
        'decimal-field--readonly': readOnly,
      }"
    >
      <div class="decimal-input-wrapper">
        <input
          v-model="localValue"
          type="number"
          @input="clearErrors()"
          :disabled="readOnly"
          :placeholder="placeholder"
          :min="min"
          :max="max"
          :step="step"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>

<style scoped lang="scss">
.decimal-field {
  display: flex;
  align-items: center;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 8px;
  position: relative;

  // ===== EDIT MODE =====
  &--edit {
    border: 1.5px solid #e5e7eb;
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 0 0.75rem;

    .decimal-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .decimal-icon {
        font-size: 1rem;
        transition: color 0.2s ease;
        color: #6b7280;
      }

      span {
        padding: 0.55rem 0;
      }

      input {
        all: unset;
        width: 100%;
        font-size: 1rem;
        letter-spacing: 0.3px;
        padding: 0.55rem 0;

        &::-webkit-inner-spin-button,
        &::-webkit-outer-spin-button {
          opacity: 0.5;
        }

        &::placeholder {
          color: #d1d5db;
          font-family: inherit;
          font-style: italic;
        }

        &:focus {
          outline: none;
        }
      }
    }

    &:focus-within {
      border-color: var(--module-color);
      background: white;

      .decimal-icon {
        color: var(--module-color);
      }
    }

    // Error state
    &.decimal-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      &:focus-within {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
      }

      .decimal-icon {
        color: var(--danger-color);
      }
    }

    // Readonly state
    &.decimal-field--readonly {
      background: #f9fafb;
      border-color: #e5e7eb;
      cursor: default;

      .decimal-icon {
        color: #9ca3af;
      }

      input {
        cursor: default;
        color: #6b7280;
      }
    }
  }

  // ===== DETAIL MODE =====
  &--detail {
    .decimal-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .decimal-detail-content {
      flex: 1;
    }
  }

  // ===== TABLE MODE =====
  &--table {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: transparent;
    border-radius: 6px;
    transition: all 0.15s ease;
  }

  // ===== SETTINGS MODE =====
  &--settings {
    max-width: 450px;
    padding: 0 0.75rem;

    .decimal-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .decimal-icon {
        color: #6b7280;
        font-size: 0.9rem;
      }

      input {
        all: unset;
        width: 100%;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        font-family: "SF Mono", "Monaco", monospace;

        &:disabled {
          background: transparent;
          color: #9ca3af;
          cursor: not-allowed;
        }
      }
    }

    .error-icon {
      margin-right: 0.5rem;
    }
  }

  // Error icon container
  .error-icon-container {
    display: flex;
    align-items: center;
    margin-left: 0.5rem;

    .error-icon {
      color: var(--danger-color);
      font-size: 1rem;
      animation: pulse 1s ease-in-out;
    }
  }
}
</style>
