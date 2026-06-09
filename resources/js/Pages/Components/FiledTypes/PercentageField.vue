<script setup>
import { computed, ref, watch } from "vue";

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
    default: 0,
  },
  max: {
    type: Number,
    default: 100,
  },
  step: {
    type: Number,
    default: 1,
  },
  showSlider: {
    type: Boolean,
    default: false,
  },
  precision: {
    type: Number,
    default: 2,
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
        emit("update:modelValue", num);
      } else {
        emit("update:modelValue", val);
      }
    }
  },
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

// Format percentage for display
const formatPercentage = (value) => {
  if (value === null || value === "" || isNaN(parseFloat(value))) return "—";
  const num = parseFloat(value);
  return `${num.toFixed(props.precision)}`;
};

// Handle input change with validation
const handleInputChange = (event) => {
  let value = event.target.value;
  value = value.replace(/%/g, "");
  localValue.value = value;
};
</script>

<template>
  <div v-if="mode === 'edit'">
    <span
      class="percentage-field percentage-field--edit"
      :class="{
        'percentage-field--error': showError,
        'percentage-field--readonly': readOnly,
      }"
    >
      <div class="percentage-input-wrapper">
        <i class="percentage-icon fa-solid fa-percent"></i>
        <span v-if="readOnly">{{ formatPercentage(localValue) }}</span>
        <div v-else class="percentage-input-container">
          <input
            v-model="localValue"
            @input="
              clearErrors();
              handleInputChange($event);
            "
            inputmode="numeric"
            class="percentage-number-input"
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
        'percentage-field percentage-field--detail display-field',
        { 'percentage-field--readonly': readOnly },
      ]"
    >
      <i class="percentage-detail-icon fa-solid fa-percent"></i>
      <div
        class="percentage-detail-content"
        v-if="modelValue !== null && modelValue !== ''"
      >
        <span class="percentage-value">{{ formatPercentage(modelValue) }}</span>
      </div>
      <div v-else>—</div>
    </div>
  </div>

  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <div class="percentage-field percentage-field--table">
      <i class="fa-solid fa-percent percentage-table-icon"></i>
      <span v-if="searchable">
        <span v-html="highlightMatch(formatPercentage(modelValue))"></span>
      </span>
      <span v-else class="percentage-table-text">
        {{ formatPercentage(modelValue) }}
      </span>
    </div>
  </div>

  <div v-if="mode === 'settings'">
    <span
      class="percentage-field percentage-field--edit percentage-field--settings"
      :class="{
        'percentage-field--error': showError,
        'percentage-field--readonly': readOnly,
      }"
    >
      <div class="percentage-input-wrapper">
        <i class="percentage-icon fa-solid fa-percent"></i>
        <div class="percentage-input-container">
          <input
            v-model="localValue"
            @input="clearErrors()"
            :disabled="readOnly"
            class="percentage-number-input"
          />
        </div>
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>
