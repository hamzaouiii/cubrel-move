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
