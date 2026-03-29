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
