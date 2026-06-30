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
    default: 1,
  },
  placeholder: {
    type: String,
  },
});

const localValue = computed({
  get: () => {
    if (props.modelValue === null || props.modelValue === "") return "";
    const num = parseInt(props.modelValue);
    return isNaN(num) ? "" : num;
  },
  set: (val) => {
    if (val === "" || val === null) {
      emit("update:modelValue", null);
    } else {
      let num = parseInt(val);
      if (!isNaN(num)) {
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

// Format integer with thousand separators
const formatInteger = (value) => {
  if (value === null || value === "" || isNaN(parseInt(value))) return "—";
  const num = parseInt(value);
  return num.toLocaleString();
};

// Handle input change
const handleInputChange = (event) => {
  let value = event.target.value;
  // Remove any non-numeric characters except minus sign
  value = value.replace(/[^\d-]/g, "");
  localValue.value = value;
};
</script>

<template>
  <div v-if="mode === 'edit'">
    <span
      class="integer-field integer-field--edit"
      :class="{
        'integer-field--error': showError,
        'integer-field--readonly': readOnly,
      }"
    >
      <div class="integer-input-wrapper">
        <span v-if="readOnly">{{ formatInteger(localValue) }}</span>
        <input
          v-else
          v-model="localValue"
          @input="
            clearErrors();
            handleInputChange($event);
          "
          :placeholder="placeholder"
          inputmode="numeric"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'dashboard'" class="df-field">
    <input v-model="localValue" inputmode="numeric" @input="clearErrors(); handleInputChange($event)" />
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'integer-field integer-field--detail display-field',
        { 'integer-field--readonly': readOnly },
      ]"
    >
      <div
        class="integer-detail-content"
        v-if="modelValue !== null && modelValue !== ''"
      >
        <span class="integer-value">{{ formatInteger(modelValue) }}</span>
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

  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <div class="integer-field integer-field--table">
      <span v-if="searchable">
        <span v-html="highlightMatch(formatInteger(modelValue))"></span>
      </span>
      <span v-else class="integer-table-text">
        {{ formatInteger(modelValue) }}
      </span>
    </div>
  </div>

  <div v-if="mode === 'settings'">
    <span
      class="integer-field integer-field--edit integer-field--settings"
      :class="{
        'integer-field--error': showError,
        'integer-field--readonly': readOnly,
      }"
    >
      <div class="integer-input-wrapper">
        <input
          v-model="localValue"
          @input="clearErrors()"
          :disabled="readOnly"
          inputmode="numeric"
          :placeholder="placeholder"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>

<style scoped lang="scss">
.integer-field {
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

    .integer-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .integer-icon {
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

      .integer-icon {
        color: var(--module-color);
      }
    }

    // Error state
    &.integer-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      &:focus-within {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
      }

      .integer-icon {
        color: var(--danger-color);
      }
    }

    // Readonly state
    &.integer-field--readonly {
      background: #f9fafb;
      border-color: #e5e7eb;
      cursor: default;

      .integer-icon {
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
    .integer-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .integer-detail-content {
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

    .integer-table-icon {
      color: #9ca3af;
      font-size: 0.8rem;
      opacity: 0.7;
    }
  }

  // ===== SETTINGS MODE =====
  &--settings {
    max-width: 450px;
    padding: 0 0.75rem;

    .integer-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .integer-icon {
        color: #6b7280;
        font-size: 0.9rem;
      }

      input {
        all: unset;
        width: 100%;
        padding: 0.5rem 0;
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
