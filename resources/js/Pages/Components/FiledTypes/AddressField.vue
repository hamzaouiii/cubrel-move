<script setup>
import { computed, ref, watch, getCurrentInstance } from "vue";
import { useClipboard } from "@/Composables/useClipboard";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import { countriesDropdown, countryMap } from "@/utils/countries";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
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
  showMap: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: Boolean,
    default: false,
  },
});

const val = computed(() => props.modelValue ?? {});

const updateSub = (key, newVal) => {
  emit("update:modelValue", { ...val.value, [key]: newVal || null });
};

const isEmpty = computed(
  () => !props.modelValue || !Object.values(props.modelValue).some(Boolean),
);

const countryLabel = computed(() => {
  if (!val.value.country) return null;
  const key = countryMap[val.value.country];
  return key ? t(key) : val.value.country;
});

const street = computed(() => val.value.street || null);
const postalCode = computed(() => val.value.postal_code || null);
const city = computed(() => val.value.city || null);
const state = computed(() => val.value.state || null);

const line2Formatted = computed(() => {
  const parts = [postalCode.value, city.value].filter(Boolean);
  return parts.length ? parts.join(" ") : null;
});

const line3Formatted = computed(() => {
  const parts = [state.value, countryLabel.value].filter(Boolean);
  return parts.length ? parts.join(", ") : null;
});

const summary = computed(() => {
  const parts = [
    street.value,
    line2Formatted.value,
    line3Formatted.value,
  ].filter(Boolean);
  return parts.length ? parts.join(", ") : "—";
});

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
  <div
    v-if="mode === 'edit' || mode === 'settings'"
    class="address-field address-field--edit"
    :class="{
      'address-field--error': showError,
      'address-field--readonly': readOnly,
      'address-field--settings': mode === 'settings',
      'address-field--has-value': !isEmpty,
    }"
  >
    <div class="address-fields-wrapper">
      <div class="address-icon-wrapper">
        <i class="address-icon fa-solid fa-location-dot"></i>
      </div>

      <div class="address-inputs">
        <div class="address-row address-row--full">
          <div class="input-group">
            <label class="input-label">{{ t("modules.address.street") }}</label>
            <input
              type="text"
              :value="val.street ?? ''"
              :disabled="readOnly"
              class="address-input address-input--street"
              @input="
                updateSub('street', $event.target.value);
                clearErrors();
              "
            />
          </div>
        </div>

        <div class="address-row address-row--split">
          <div class="input-group input-group--small">
            <label class="input-label">{{
              t("modules.address.postal_code")
            }}</label>
            <input
              type="text"
              :value="val.postal_code ?? ''"
              :disabled="readOnly"
              class="address-input address-input--postal"
              @input="
                updateSub('postal_code', $event.target.value);
                clearErrors();
              "
            />
          </div>

          <div class="input-group input-group--grow">
            <label class="input-label">{{ t("modules.address.city") }}</label>
            <input
              type="text"
              :value="val.city ?? ''"
              :disabled="readOnly"
              class="address-input address-input--city"
              @input="
                updateSub('city', $event.target.value);
                clearErrors();
              "
            />
          </div>
        </div>

        <div class="address-row address-row--split">
          <div class="input-group input-group--grow">
            <label class="input-label">{{ t("modules.address.state") }}</label>
            <input
              type="text"
              :value="val.state ?? ''"
              :disabled="readOnly"
              class="address-input address-input--state"
              @input="
                updateSub('state', $event.target.value);
                clearErrors();
              "
            />
          </div>

          <div class="input-group input-group--grow">
            <label class="input-label">{{
              t("modules.address.country")
            }}</label>
            <Select
              :modelValue="val.country ?? null"
              :dropdown_list="countriesDropdown"
              :searchable="true"
              :nullable="true"
              :disabled="readOnly"
              mode="edit"
              class="address-country-select"
              @update:modelValue="
                updateSub('country', $event);
                clearErrors();
              "
            />
          </div>
        </div>
      </div>
    </div>

    <span v-if="showError" class="error-icon-container">
      <i class="error-icon fa-solid fa-circle-exclamation"></i>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      class="address-field address-field--detail display-field"
      :class="{
        'address-field--readonly': readOnly,
        'address-field--empty': isEmpty,
      }"
    >
      <i class="address-detail-icon fa-solid fa-location-dot"></i>

      <div class="address-detail-content" v-if="!isEmpty">
        <div class="address-detail-line" v-if="street">
          <i class="fa-solid fa-road"></i>
          <span>{{ street }}</span>
        </div>
        <div class="address-detail-line" v-if="line2Formatted">
          <i class="fa-solid fa-envelope"></i>
          <span>{{ line2Formatted }}</span>
        </div>
        <div class="address-detail-line" v-if="line3Formatted">
          <i class="fa-solid fa-globe"></i>
          <span>{{ line3Formatted }}</span>
        </div>
      </div>

      <div v-else class="address-empty">
        <span>—</span>
      </div>

      <div class="address-actions">
        <button
          v-if="!isEmpty && !readOnly"
          class="copy-btn"
          @click.stop.prevent="copy(summary)"
          :title="copied ? 'Copied!' : 'Copy address'"
        >
          <i
            :class="
              copied ? 'fa-solid fa-check text-success' : 'fa-regular fa-copy'
            "
          ></i>
        </button>
      </div>
    </div>
  </div>

  <div v-else-if="mode === 'table'">
    <div class="address-field address-field--table">
      <i class="fa-solid fa-location-dot address-table-icon"></i>
      <span v-if="searchable" v-html="highlightMatch(summary)"></span>
      <span v-else class="address-table-text">{{ summary }}</span>
    </div>
  </div>

  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    <div class="address-field address-field--related">
      <i class="fa-solid fa-location-dot address-related-icon"></i>
      <span class="address-related-text">{{ summary }}</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.address-field {
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

    .address-fields-wrapper {
      display: flex;
      gap: 0.75rem;
      width: 100%;
    }

    .address-icon-wrapper {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;

      .address-icon {
        font-size: 1rem;
        color: #94a3b8;
      }
    }

    .address-inputs {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .address-row {
      display: flex;
      gap: 0.75rem;
      width: 100%;

      &--full {
        width: 100%;
      }

      &--split {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.75rem;

        @media (max-width: 480px) {
          grid-template-columns: 1fr;
          gap: 0.5rem;
        }
      }
    }

    .input-group {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;

      &--small {
        max-width: 120px;

        @media (max-width: 480px) {
          max-width: 100%;
        }
      }

      &--grow {
        flex: 1;
      }

      .input-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        transition: color 0.2s ease;
      }
    }

    .address-input {
      all: unset;
      width: 100%;
      font-size: 0.9rem;
      font-family: inherit;
      padding: 0.5rem 0;
      color: #1e293b;
      border-bottom: 1.5px solid #e2e8f0;
      transition: all 0.2s ease;
      background: transparent;

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

    .address-country-select {
      width: 100%;

      :deep(.select-field__control) {
        padding: 0.5rem 0;
        border: none;
        border-bottom: 1.5px solid #e2e8f0;
        border-radius: 0;
        background: transparent;
        min-height: unset;
        transition: border-bottom-color 0.2s ease;

        &.is-open {
          border-bottom-color: var(--module-color);
        }

        &:hover {
          border-bottom-color: #cbd5e1;
        }
      }

      :deep(.select-field__selected) {
        font-size: 0.9rem;
        color: #1e293b;
        padding: 0;
      }

      :deep(.select-field__icons) {
        position: absolute;
        right: 0;
      }
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
    &.address-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      .input-label {
        color: #ef4444;
      }
    }

    // Readonly state
    &.address-field--readonly {
      background: #f8fafc;
      border-color: #e2e8f0;

      .address-input {
        color: #64748b;
        cursor: default;
      }
    }

    // Has value state
    &.address-field--has-value {
      .address-input {
        font-weight: 500;
      }
    }

    &.address-field--settings {
      max-width: 550px;
    }
  }

  // ===== DETAIL MODE =====
  &--detail {
    .address-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .address-detail-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;

      .address-detail-line {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        line-height: 1.4;

        i {
          width: 20px;
          color: #94a3b8;
          font-size: 0.8rem;
          flex-shrink: 0;
        }

        span {
          flex: 1;
        }
      }
    }

    .address-empty {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .address-actions {
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
    max-width: 300px;
    cursor: default;

    .address-table-icon {
      color: #9ca3af;
      font-size: 0.8rem;
      opacity: 0.7;
      flex-shrink: 0;
    }

    .address-table-text {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
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

    .address-related-icon {
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

// Animation
@keyframes pulse {
  0% {
    opacity: 0.6;
    transform: scale(0.95);
  }
  50% {
    opacity: 1;
    transform: scale(1.05);
  }
  100% {
    opacity: 0.6;
    transform: scale(0.95);
  }
}
</style>
