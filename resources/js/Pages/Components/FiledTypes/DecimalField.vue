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
          inputmode=""
          @input="
            clearErrors();
            handleInputChange($event);
          "
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

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
