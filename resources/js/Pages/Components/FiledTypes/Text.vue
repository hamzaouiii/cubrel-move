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
</script>

<template>
  <div v-if="mode === 'settings'">
    <span
      class="text-field text-field--edit text-field--settings"
      :class="{
        'text-field--error': showError,
        'text-field--readonly': readOnly,
      }"
    >
      <div class="text-input-wrapper">
        <input
          v-model="localValue"
          type="text"
          @input="clearErrors()"
          :disabled="readOnly"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
  <div v-else-if="readOnly">
    {{ modelValue }}
  </div>
  <div v-else-if="mode === 'edit'">
    <span
      class="text-field text-field--edit edit-field"
      :class="{
        'text-field--error': showError,
        'text-field--readonly': readOnly,
      }"
    >
      <div class="text-input-wrapper">
        <span v-if="readOnly">{{ localValue }}</span>
        <input v-else v-model="localValue" type="text" @input="clearErrors()" />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'dashboard'" class="df-field">
    <input v-model="localValue" type="text" @input="clearErrors()" />
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'text-field text-field--detail display-field',
        { 'text-field--readonly': readOnly },
      ]"
    >
      <div class="text-detail-content">
        <span class="text-value">{{ modelValue || "—" }}</span>
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
    <div class="text-field text-field--table">
      <span v-if="searchable">
        <span v-html="highlightMatch(modelValue ?? '—')"></span>
      </span>
      <span v-else class="text-table-text">
        {{ modelValue || "—" }}
      </span>
    </div>
  </div>
</template>
