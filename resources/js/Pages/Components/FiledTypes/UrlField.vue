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

// Extract domain from URL for display
const getDomain = (url) => {
  if (!url) return "";
  try {
    const urlObj = new URL(url.toString());
    return urlObj.hostname;
  } catch {
    return url.toString().split("/")[0];
  }
};

// Get protocol from URL
const getProtocol = (url) => {
  if (!url) return "";
  try {
    const urlObj = new URL(url.toString());
    return urlObj.protocol;
  } catch {
    return "";
  }
};

// Open URL in new tab
const openUrl = (url) => {
  if (url && !props.readOnly) {
    window.open(url.toString(), "_blank", "noopener,noreferrer");
  }
};
</script>

<template>
  <!-- Edit Mode -->
  <div v-if="mode === 'edit'">
    <span
      class="url-field url-field--edit"
      :class="{
        'url-field--error': showError,
        'url-field--readonly': readOnly,
      }"
    >
      <div class="url-input-wrapper">
        <i class="url-icon fa-solid fa-link"></i>
        <span v-if="readOnly">{{ localValue || "—" }}</span>
        <input
          v-else
          v-model="localValue"
          @input="clearErrors()"
          placeholder="https://example.com"
          type="url"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <!-- Detail Mode -->
  <div v-else-if="mode === 'detail'">
    <div
      :class="[
        'url-field url-field--detail display-field',
        { 'url-field--readonly': readOnly },
      ]"
    >
      <i class="url-detail-icon fa-solid fa-link"></i>
      <div class="url-detail-content" v-if="modelValue">
        <span v-if="modelValue" class="url-link">
          <span class="url-protocol" v-if="getProtocol(modelValue)">
            {{ getProtocol(modelValue) }}//
          </span>
          <span class="url-domain">{{ getDomain(modelValue) }}</span>
          <span
            class="url-path"
            v-if="modelValue.toString().split('/').slice(3).join('/')"
          >
            /{{ modelValue.toString().split("/").slice(3).join("/") }}
          </span>
        </span>
        <span v-else>{{ modelValue }}</span>
      </div>
      <div v-else>—</div>
      <div class="url-actions">
        <button
          v-if="modelValue && !readOnly && String(modelValue).length > 0"
          class="copy-btn"
          @click.stop.prevent="copy(modelValue)"
          :title="copied ? 'Copied!' : 'Copy URL'"
        >
          <i
            :class="
              copied ? 'fa-solid fa-check text-success' : 'fa-regular fa-copy'
            "
          ></i>
        </button>
        <button
          v-if="modelValue && !readOnly && String(modelValue).length > 0"
          class="open-btn"
          @click.stop.prevent="openUrl(modelValue)"
          title="Open URL"
        >
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Table/Related Panel/Linking Panel Mode -->
  <div
    v-else-if="
      mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'
    "
  >
    <div class="url-field url-field--table">
      <i class="fa-solid fa-link url-table-icon"></i>
      <span v-if="searchable">
        <span v-html="highlightMatch(modelValue ?? '—')"></span>
      </span>
      <a
        v-else
        :href="modelValue"
        target="_blank"
        rel="noopener noreferrer"
        class="url-table-link"
        @click.stop
      >
        {{ getDomain(modelValue) || "—" }}
      </a>
    </div>
  </div>

  <!-- Settings Mode -->
  <div v-if="mode === 'settings'">
    <span
      class="url-field url-field--edit url-field--settings"
      :class="{
        'url-field--error': showError,
        'url-field--readonly': readOnly,
      }"
    >
      <div class="url-input-wrapper">
        <i class="url-icon fa-solid fa-link"></i>
        <input
          v-model="localValue"
          type="url"
          @input="clearErrors()"
          :disabled="readOnly"
          placeholder="https://example.com"
        />
      </div>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>
</template>
