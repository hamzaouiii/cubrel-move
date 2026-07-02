<!-- LongText.vue -->
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
  rows: {
    type: Number,
    default: 3,
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

const getDynamicRows = () => {
  if (!localValue.value) return props.rows;

  const text = String(localValue.value);
  const lines = text.split("\n").length;
  const words = text.split(" ").length;
  const estimatedLines = Math.ceil(words / 8);

  return Math.min(Math.max(lines, estimatedLines, props.rows), 10);
};

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  const truncatedText =
    text.toString().length > 100
      ? text.toString().substring(0, 100) + "..."
      : text.toString();

  return truncatedText.replace(
    regex,
    '<span class="search-highlight">$1</span>',
  );
};
</script>

<template>
  <div v-if="readOnly">
    {{ modelValue }}
  </div>
  <div v-else-if="mode === 'edit'">
    <span
      class="long-text-field long-text-field--edit"
      :class="{
        'long-text-field--error': showError,
        'long-text-field--readonly': readOnly,
      }"
    >
      <div class="long-text-input-wrapper">
        <textarea
          v-model="localValue"
          @input="clearErrors()"
          :readonly="readOnly"
          :rows="getDynamicRows()"
        ></textarea>
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'long-text-field long-text-field--detail display-field',
        { 'long-text-field--readonly': readOnly },
      ]"
    >
      <div class="long-text-detail-content">
        <pre class="long-text-value">{{ modelValue || "—" }}</pre>
      </div>

      <button
        v-if="modelValue && !readOnly && String(modelValue).length > 0"
        class="copy-btn"
        @click.stop.prevent="copy(modelValue)"
        :title="copied ? 'Copied!' : 'Copy text'"
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
    <div class="long-text-field long-text-field--table">
      <span v-if="searchable">
        <span v-html="highlightMatch(modelValue ?? '—')"></span>
      </span>
      <span v-else class="long-text-table-text">
        {{ (modelValue ?? "").toString().substring(0, 64) || "—" }}
        {{ (modelValue ?? "").toString().length > 64 ? "..." : "" }}
      </span>
    </div>
  </div>

  <div v-if="mode === 'settings'">
    <span
      class="long-text-field long-text-field--edit long-text-field--settings"
      :class="{
        'long-text-field--error': showError,
        'long-text-field--readonly': readOnly,
      }"
    >
      <div class="long-text-input-wrapper">
        <i class="long-text-icon fa-regular fa-rectangle-adjust"></i>
        <textarea
          v-model="localValue"
          @input="clearErrors()"
          :disabled="readOnly"
          :rows="rows"
        ></textarea>
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>
