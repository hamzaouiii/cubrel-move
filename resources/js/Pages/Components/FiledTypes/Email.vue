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

const getEmailDomain = (email) => {
  if (!email) return "";
  const parts = email.toString().split("@");
  return parts.length > 1 ? parts[1] : "";
};

const getEmailLocalPart = (email) => {
  if (!email) return "";
  const parts = email.toString().split("@");
  return parts[0] || "";
};
</script>

<template>
  <div v-if="mode === 'edit'">
    <span
      class="email-field email-field--edit"
      :class="{
        'email-field--error': showError,
        'email-field--readonly': readOnly,
      }"
    >
      <div class="email-input-wrapper">
        <i class="email-icon fa-regular fa-envelope"></i>
        <span v-if="readOnly">{{ localValue }}</span>

        <input v-else v-model="localValue" @input="clearErrors()" />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'email-field email-field--detail display-field',
        { 'email-field--readonly': readOnly },
      ]"
    >
      <i class="email-detail-icon fa-regular fa-envelope"></i>
      <div class="email-detail-content" v-if="modelValue">
        <span class="email-local-part">{{
          getEmailLocalPart(modelValue)
        }}</span>
        <span class="email-at-sign">@</span>
        <span class="email-domain">{{ getEmailDomain(modelValue) }}</span>
      </div>
      <div v-else>—</div>
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
    <div class="email-field email-field--table">
      <i class="fa-regular fa-envelope email-table-icon"></i>
      <span v-if="searchable">
        <span v-html="highlightMatch(modelValue ?? '—')"></span>
      </span>
      <span v-else class="email-table-text">
        {{ modelValue || "—" }}
      </span>
    </div>
  </div>

  <div v-if="mode === 'settings'">
    <span
      class="email-field email-field--edit email-field--settings"
      :class="{
        'email-field--error': showError,
        'email-field--readonly': readOnly,
      }"
    >
      <div class="email-input-wrapper">
        <i class="email-icon fa-regular fa-envelope"></i>
        <input
          v-model="localValue"
          type="email"
          @input="clearErrors()"
          :disabled="readOnly"
          placeholder="email@example.com"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>
