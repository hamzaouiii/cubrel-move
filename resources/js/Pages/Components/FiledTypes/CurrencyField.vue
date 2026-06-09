<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useClipboard } from "@/Composables/useClipboard";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: { default: null },
  mode: { type: String, default: "edit" },
  hasError: { type: Boolean, default: false },
  readOnly: { type: Boolean, default: false },
  highlight: String,
  searchable: Boolean,
});

const page = usePage();
const currencyCode = computed(
  () => page.props.appSettings?.default_currency || "EUR",
);

const raw = computed(() =>
  props.modelValue === null || props.modelValue === undefined
    ? ""
    : String(props.modelValue),
);

const isEmpty = computed(() => raw.value === "");

const formatDisplay = (val, code) => {
  if (val === null || val === "" || val === undefined) return "—";
  const num = parseFloat(val);
  if (isNaN(num)) return "—";
  try {
    return new Intl.NumberFormat(undefined, {
      style: "currency",
      currency: code,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(num);
  } catch {
    return `${code} ${num.toFixed(2)}`;
  }
};

const displayValue = computed(() =>
  formatDisplay(props.modelValue, currencyCode.value),
);

const currencyParts = computed(() => {
  const val = props.modelValue;
  if (val === null || val === "" || val === undefined) return null;
  const num = parseFloat(val);
  if (isNaN(num)) return null;
  try {
    const parts = new Intl.NumberFormat(undefined, {
      style: "currency",
      currency: currencyCode.value,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).formatToParts(num);
    const symbol =
      parts.find((p) => p.type === "currency")?.value ?? currencyCode.value;
    const amount = parts
      .filter((p) => p.type !== "currency")
      .map((p) => p.value)
      .join("")
      .trim();
    return { symbol, amount };
  } catch {
    return { symbol: currencyCode.value, amount: num.toFixed(2) };
  }
});

const onInput = (e) => {
  const cleaned = e.target.value.replace(/[^\d.-]/g, "");
  emit("update:modelValue", cleaned === "" ? null : cleaned);
  showError.value = false;
};

const showError = ref(false);
const { copied, copy } = useClipboard();

watch(
  () => props.hasError,
  (v) => {
    showError.value = v;
  },
  { immediate: true },
);

const clearErrors = () => {
  showError.value = false;
};

// Highlight match for search
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
  <!-- Edit / Settings Mode -->
  <div
    v-if="mode === 'edit' || mode === 'settings'"
    class="currency-field currency-field--edit"
    :class="{
      'currency-field--error': showError,
      'currency-field--readonly': readOnly,
      'currency-field--settings': mode === 'settings',
      'currency-field--has-value': !isEmpty,
    }"
  >
    <div class="currency-fields-wrapper">
      <div class="currency-icon-wrapper">
        <i class="currency-icon fa-solid fa-coins"></i>
      </div>

      <div class="currency-inputs">
        <div class="currency-row currency-row--full">
          <div class="input-group">
            <input
              v-if="!readOnly"
              type="text"
              inputmode="decimal"
              :value="raw"
              class="currency-input"
              @input="onInput"
            />
            <span v-else class="currency-display">{{ displayValue }}</span>
          </div>
        </div>
      </div>
    </div>

    <span v-if="showError" class="error-icon-container">
      <i class="error-icon fa-solid fa-circle-exclamation"></i>
    </span>
  </div>

  <!-- Detail Mode -->
  <div
    v-else-if="mode === 'detail'"
    class="currency-field currency-field--detail display-field"
    :class="{
      'currency-field--readonly': readOnly,
      'currency-field--empty': isEmpty,
    }"
  >
    <i class="currency-detail-icon fa-solid fa-coins"></i>

    <div class="currency-detail-content" v-if="!isEmpty && currencyParts">
      <div class="currency-detail-line">
        <span class="currency-code-badge">{{ currencyParts.symbol }}</span>

        <span class="currency-amount-value">{{ currencyParts.amount }}</span>
      </div>
    </div>

    <div v-else class="currency-empty">
      <span>—</span>
    </div>

    <div class="currency-actions">
      <button
        v-if="!isEmpty && !readOnly"
        class="copy-btn"
        @click.stop.prevent="copy(displayValue)"
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

  <!-- Table Mode -->
  <div v-else-if="mode === 'table'">
    <div class="currency-field currency-field--table">
      <i class="fa-solid fa-coins currency-table-icon"></i>
      <span v-if="searchable" v-html="highlightMatch(displayValue)"></span>
      <span v-else class="currency-table-text">{{ displayValue }}</span>
    </div>
  </div>

  <!-- Related Panel / Linking Panel Mode -->
  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    <div class="currency-field currency-field--related">
      <i class="fa-solid fa-coins currency-related-icon"></i>
      <span class="currency-related-text">{{ displayValue }}</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.currency-field {
  border-radius: 8px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;

  // ===== EDIT / SETTINGS MODE =====
  &--edit {
    border: 1.5px solid #e5e7eb;
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 0.75rem;
    position: relative;
    transition: all 0.2s ease;

    .currency-fields-wrapper {
      display: flex;
      gap: 0.75rem;
      width: 100%;
    }

    .currency-icon-wrapper {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;

      .currency-icon {
        font-size: 1rem;
        color: #94a3b8;
      }
    }

    .currency-inputs {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .currency-row {
      display: flex;
      gap: 0.75rem;
      width: 100%;

      &--full {
        width: 100%;
      }
    }

    .input-group {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;

      .input-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
      }
    }

    .currency-input {
      all: unset;
      width: 100%;
      font-family: inherit;

      &:focus {
        border-bottom-color: var(--module-color);
        outline: none;
      }

      &:disabled {
        color: #94a3b8;
        cursor: default;
        border-bottom-color: #f1f5f9;
      }

      &:hover:not(:disabled) {
        border-bottom-color: #cbd5e1;
      }
    }

    .currency-display {
      font-size: 0.9rem;
      padding: 0.5rem 0;
      color: #1e293b;
      font-variant-numeric: tabular-nums;
      display: block;
    }

    &:hover {
      border-color: #cbd5e1;
      background: white;
    }

    &:focus-within {
      border-color: var(--module-color);
      background: white;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.08);
    }

    // Error state
    &.currency-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      .input-label {
        color: #ef4444;
      }

      .currency-icon {
        color: #ef4444;
      }
    }

    // Readonly state
    &.currency-field--readonly {
      background: #f8fafc;
      border-color: #e2e8f0;

      .currency-display {
        color: #64748b;
      }
    }

    // Has value state
    &.currency-field--has-value {
      .currency-input {
        font-weight: 500;
      }
    }

    &.currency-field--settings {
      max-width: 450px;
    }
  }

  // ===== DETAIL MODE =====
  &--detail {
    .currency-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .currency-detail-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;

      .currency-detail-line {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #1e293b;
        line-height: 1.4;

        .currency-code-badge {
          font-size: 0.95rem;
          font-weight: 700;
          text-transform: uppercase;
          color: var(--module-color);
          background: color-mix(in srgb, var(--module-color) 10%, white);
          border: 1px solid color-mix(in srgb, var(--module-color) 20%, white);
          border-radius: 4px;
          padding: 0.2rem 0.5rem;
          flex-shrink: 0;
          user-select: none;
        }
      }
    }

    .currency-empty {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .currency-actions {
      display: flex;
      gap: 0.5rem;
      flex-shrink: 0;

      .copy-btn {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.375rem 0.625rem;
        cursor: pointer;
        color: #64748b;
        font-size: 0.875rem;
        transition: all 0.2s ease;

        &:hover {
          background: var(--module-color);
          border-color: var(--module-color);
          color: white;
          transform: scale(1.05);
        }
      }
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
    cursor: default;

    .currency-table-icon {
      color: #9ca3af;
      font-size: 0.98rem;
      opacity: 0.7;
      flex-shrink: 0;
    }
  }

  // ===== RELATED PANEL MODE =====
  &--related {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: transparent;
    border-radius: 6px;

    .currency-related-icon {
      color: #9ca3af;
      font-size: 0.8rem;
      flex-shrink: 0;
    }
  }

  // Error icon container
  .error-icon-container {
    display: flex;
    align-items: center;
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;

    .error-icon {
      color: var(--danger-color);
      font-size: 1rem;
      animation: pulse 1s ease-in-out;
    }
  }
}
</style>
