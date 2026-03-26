<script setup>
import { computed, ref, watch } from "vue";
import { useClipboard } from "@/Composables/useClipboard";
import { parsePhoneNumberFromString, AsYouType } from "libphonenumber-js";

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
  // Pass a default ISO country code here (e.g., 'US', 'GB', 'DE')
  countryCode: {
    type: String,
  },
  format: {
    type: String,
    default: "international", // 'international', 'national', 'e164'
  },
});

const localValue = computed({
  get: () => props.modelValue ?? "",
  set: (val) => emit("update:modelValue", val),
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
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};

// Format phone number for display
const formatPhoneNumber = (phoneNumber) => {
  if (!phoneNumber) return "";

  const parsedNumber = parsePhoneNumberFromString(
    String(phoneNumber),
    props.countryCode,
  );

  if (parsedNumber) {
    if (props.format === "e164") return parsedNumber.format("E.164");
    if (props.format === "national") return parsedNumber.formatNational();
    return parsedNumber.formatInternational();
  }

  // Return as-is if it's too invalid to parse
  return phoneNumber;
};

// Get formatted display parts (Refactored to handle global formats safely)
const getPhoneParts = (phoneNumber) => {
  if (!phoneNumber)
    return { countryCallingCode: "", nationalNumber: "", raw: "" };

  const parsedNumber = parsePhoneNumberFromString(
    String(phoneNumber),
    props.countryCode,
  );

  if (parsedNumber) {
    return {
      countryCallingCode: `+${parsedNumber.countryCallingCode}`,
      nationalNumber: parsedNumber.formatNational(),
      raw: phoneNumber,
    };
  }

  return {
    countryCallingCode: "",
    nationalNumber: phoneNumber,
    raw: phoneNumber,
  };
};

// Input formatting as user types (Using AsYouType formatter)
const formatInput = (event) => {
  const inputValue = event.target.value;

  // AsYouType intelligently formats based on the default country or typed '+' code
  const formatter = new AsYouType(props.countryCode);
  localValue.value = formatter.input(inputValue);
};
</script>

<template>
  <div v-if="mode === 'edit'">
    <span
      class="phone-field phone-field--edit"
      :class="{
        'phone-field--error': showError,
        'phone-field--readonly': readOnly,
      }"
    >
      <div class="phone-input-wrapper">
        <i class="phone-icon fa-solid fa-phone"></i>
        <span v-if="readOnly">{{ localValue || "—" }}</span>

        <div v-else class="phone-input-container">
          <span class="country-code-badge" v-if="countryCode">
            {{ countryCode }}
          </span>
          <input
            v-model="localValue"
            @input="
              clearErrors();
              formatInput($event);
            "
            placeholder="+1 555 555-5555"
            type="tel"
          />
        </div>
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'phone-field phone-field--detail display-field',
        { 'phone-field--readonly': readOnly },
      ]"
    >
      <i class="phone-detail-icon fa-solid fa-phone"></i>
      <div class="phone-detail-content" v-if="modelValue">
        <template v-if="getPhoneParts(modelValue).countryCallingCode">
          <span class="phone-country-code">{{
            getPhoneParts(modelValue).countryCallingCode
          }}</span>
          <span class="phone-separator"> </span>
          <span class="phone-national-number">{{
            getPhoneParts(modelValue).nationalNumber
          }}</span>
        </template>
        <template v-else>
          {{ formatPhoneNumber(modelValue) }}
        </template>
      </div>
      <div v-else>—</div>
      <button
        v-if="modelValue && !readOnly && String(modelValue).length > 0"
        class="copy-btn"
        @click.stop.prevent="copy(modelValue)"
        :title="copied ? 'Copied!' : 'Copy number'"
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
    <div class="phone-field phone-field--table">
      <i class="fa-solid fa-phone phone-table-icon"></i>
      <span v-if="searchable">
        <span v-html="highlightMatch(modelValue ?? '—')"></span>
      </span>
      <span v-else class="phone-table-text">
        {{ modelValue ? formatPhoneNumber(modelValue) : "—" }}
      </span>
    </div>
  </div>

  <div v-if="mode === 'settings'">
    <span
      class="phone-field phone-field--edit phone-field--settings"
      :class="{
        'phone-field--error': showError,
        'phone-field--readonly': readOnly,
      }"
    >
      <div class="phone-input-wrapper">
        <i class="phone-icon fa-solid fa-phone"></i>
        <input
          v-model="localValue"
          type="tel"
          @input="
            clearErrors();
            formatInput($event);
          "
          :disabled="readOnly"
          placeholder="+1 555 555-5555"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>

<style scoped lang="scss">
/* Keep your exact same SCSS styles here! 
   You only need to update the classes in the detail view styling slightly 
   if you want to target `.phone-national-number` instead of prefix/line-number */
</style>
<style scoped lang="scss">
.phone-field {
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

    .phone-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .phone-icon {
        font-size: 1rem;
        transition: color 0.2s ease;
        color: #6b7280;
      }

      span {
        padding: 0.55rem 0;
      }

      .phone-input-container {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 0.5rem;

        .country-code-badge {
          font-size: 0.875rem;
          font-weight: 600;
          color: var(--module-color);
          background: rgba(59, 130, 246, 0.1);
          padding: 0.25rem 0.5rem;
          border-radius: 6px;
          font-family: monospace;
        }

        input {
          all: unset;
          width: 100%;
          font-size: 1rem;
          letter-spacing: 0.3px;
          padding: 0.55rem 0;
          font-family: "SF Mono", "Monaco", "Cascadia Code", monospace;

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
    }

    &:focus-within {
      border-color: var(--module-color);
      background: white;

      .phone-icon {
        color: var(--module-color);
      }
    }

    // Error state
    &.phone-field--error {
      border-color: var(--danger-color);
      background: #fef2f2;

      &:focus-within {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
      }

      .phone-icon {
        color: var(--danger-color);
      }
    }

    // Readonly state
    &.phone-field--readonly {
      background: #f9fafb;
      border-color: #e5e7eb;
      cursor: default;

      .phone-icon {
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
    .phone-detail-icon {
      color: var(--module-color);
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .phone-detail-content {
      flex: 1;
      font-family: "SF Mono", "Monaco", "Cascadia Code", monospace;
      display: flex;
      align-items: baseline;
      flex-wrap: wrap;
      gap: 0.125rem;

      .phone-country-code {
        color: var(--module-color);
        font-weight: 700;
        background: rgba(59, 130, 246, 0.1);
        padding: 0.125rem 0.375rem;
        border-radius: 4px;
        font-size: 0.875rem;
      }

      .phone-area-code {
        color: #1e293b;
        font-weight: 600;
      }

      .phone-prefix,
      .phone-line-number {
        color: #334155;
      }

      .phone-separator {
        color: #94a3b8;
        margin: 0 0.125rem;
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

    .phone-table-icon {
      color: #9ca3af;
      font-size: 0.8rem;
      opacity: 0.7;
    }

    .phone-table-text {
      font-family: "SF Mono", "Monaco", monospace;
      font-size: 0.875rem;
      color: #1f2937;
    }

    &:hover {
      background: #f3f4f6;

      .phone-table-icon {
        opacity: 1;
        color: var(--module-color);
      }
    }
  }

  // ===== SETTINGS MODE =====
  &--settings {
    max-width: 450px;
    padding: 0 0.75rem;

    .phone-input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 0.5rem;

      .phone-icon {
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

        &::placeholder {
          font-family: inherit;
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

// Global display-field styling is handled by parent component
.display-field {
  .copy-btn {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.375rem 0.625rem;
    cursor: pointer;
    color: #64748b;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    flex-shrink: 0;

    &:hover {
      background: var(--module-color);
      border-color: var(--module-color);
      color: white;
      transform: scale(1.05);
    }

    &:active {
      transform: scale(0.98);
    }
  }
}

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
