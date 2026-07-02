<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useClipboard } from "@/Composables/useClipboard";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: { default: null },
  mode: { type: String, default: "edit" },
  hasError: { type: Boolean, default: false },
  readOnly: { type: Boolean, default: false },
  highlight: String,
  searchable: Boolean,
});

const page = usePage();
const currencyCode = computed(
  () => page.props.appSettings?.default_currency || "EUR",
);

const raw = computed(() =>
  props.modelValue === null || props.modelValue === undefined
    ? ""
    : String(props.modelValue),
);

const isEmpty = computed(() => raw.value === "");

const formatDisplay = (val, code) => {
  if (val === null || val === "" || val === undefined) return "—";
  const num = parseFloat(val);
  if (isNaN(num)) return "—";
  try {
    return new Intl.NumberFormat(undefined, {
      style: "currency",
      currency: code,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(num);
  } catch {
    return `${code} ${num.toFixed(2)}`;
  }
};

const displayValue = computed(() =>
  formatDisplay(props.modelValue, currencyCode.value),
);

const currencyParts = computed(() => {
  const val = props.modelValue;
  if (val === null || val === "" || val === undefined) return null;
  const num = parseFloat(val);
  if (isNaN(num)) return null;
  try {
    const parts = new Intl.NumberFormat(undefined, {
      style: "currency",
      currency: currencyCode.value,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).formatToParts(num);
    const symbol =
      parts.find((p) => p.type === "currency")?.value ?? currencyCode.value;
    const amount = parts
      .filter((p) => p.type !== "currency")
      .map((p) => p.value)
      .join("")
      .trim();
    return { symbol, amount };
  } catch {
    return { symbol: currencyCode.value, amount: num.toFixed(2) };
  }
});

const onInput = (e) => {
  const cleaned = e.target.value.replace(/[^\d.-]/g, "");
  emit("update:modelValue", cleaned === "" ? null : cleaned);
  showError.value = false;
};

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
  <!-- Edit / Settings Mode -->
  <div
    v-if="mode === 'edit' || mode === 'settings' || mode === 'dashboard'"
    class="currency-field currency-field--edit"
    :class="{
      'currency-field--error': showError,
      'currency-field--readonly': readOnly,
      'currency-field--settings': mode === 'settings',
      'currency-field--has-value': !isEmpty,
    }"
  >
    <div class="currency-fields-wrapper">
      <div class="currency-icon-wrapper">
        <i class="currency-icon fa-solid fa-coins"></i>
      </div>

      <div class="currency-inputs">
        <div class="currency-row currency-row--full">
          <div class="input-group">
            <input
              v-if="!readOnly"
              type="text"
              inputmode="decimal"
              :value="raw"
              class="currency-input"
              @input="onInput"
            />
            <span v-else class="currency-display">{{ displayValue }}</span>
          </div>
        </div>
      </div>
    </div>

    <span v-if="showError" class="error-icon-container">
      <i class="error-icon fa-solid fa-circle-exclamation"></i>
    </span>
  </div>

  <!-- Detail Mode -->
  <div
    v-else-if="mode === 'detail'"
    class="currency-field currency-field--detail display-field"
    :class="{
      'currency-field--readonly': readOnly,
      'currency-field--empty': isEmpty,
    }"
  >
    <i class="currency-detail-icon fa-solid fa-coins"></i>

    <div class="currency-detail-content" v-if="!isEmpty && currencyParts">
      <div class="currency-detail-line">
        <span class="currency-code-badge">{{ currencyParts.symbol }}</span>

        <span class="currency-amount-value">{{ currencyParts.amount }}</span>
      </div>
    </div>

    <div v-else class="currency-empty">
      <span>—</span>
    </div>

    <div class="currency-actions">
      <button
        v-if="!isEmpty && !readOnly"
        class="copy-btn"
        @click.stop.prevent="copy(displayValue)"
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

  <!-- Table Mode -->
  <div v-else-if="mode === 'table'">
    <div class="currency-field currency-field--table">
      <i class="fa-solid fa-coins currency-table-icon"></i>
      <span v-if="searchable" v-html="highlightMatch(displayValue)"></span>
      <span v-else class="currency-table-text">{{ displayValue }}</span>
    </div>
  </div>

  <!-- Related Panel / Linking Panel Mode -->
  <div v-else-if="mode === 'related-panel' || mode === 'linkingPanel'">
    <div class="currency-field currency-field--related">
      <i class="fa-solid fa-coins currency-related-icon"></i>
      <span class="currency-related-text">{{ displayValue }}</span>
    </div>
  </div>
</template>
