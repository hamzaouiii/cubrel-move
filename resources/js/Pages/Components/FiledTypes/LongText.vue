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
});
const localValue = computed({
  get: () => props.modelValue ?? "",
  set: (val) => emit("update:modelValue", val),
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
const getRows = () => {
  if (localValue.value) {
    const val = localValue.value.split(" ").length;
    return Math.max(val / 8, 3);
  }
  return 3;
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
  <div v-if="mode === 'edit'">
    <span
      class="text-field text-field--edit"
      :class="{
        'text-field--error': showError,
        'text-field--readonly': readOnly,
      }"
    >
      <textarea
        v-model="localValue"
        type="text"
        @input="clearErrors()"
        :rows="getRows()"
      ></textarea>

      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
  <div v-if="mode === 'settings'">
    <span
      class="text-field text-field--edit text-field--settings"
      :class="{
        'text-field--error': showError,
        'text-field--readonly': readOnly,
      }"
    >
      <textarea
        v-model="localValue"
        type="text"
        @input="clearErrors()"
        :rows="getRows()"
      ></textarea>

      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <span :class="['text-field', { 'view-uneditable-field': readOnly }]">
      {{ modelValue }}
    </span>
  </div>

  <div v-else-if="mode === 'table'">
    <span>
      <span
        v-html="highlightMatch(modelValue.substring(0, 64) + '...' ?? '—')"
      ></span>
    </span>
  </div>

  <div v-else-if="mode === 'related-panel'">
    <span>
      {{ modelValue.substring(0, 32) + "..." || "—" }}
    </span>
  </div>
</template>
