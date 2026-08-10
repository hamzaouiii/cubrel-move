<script setup>
import { computed, ref, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
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
  moduleColor: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: "",
  },
  highlight: String,
  searchable: Boolean,
});

const values = computed(() => (Array.isArray(props.modelValue) ? props.modelValue : []));
const color = computed(() => props.moduleColor || appSettings.primary_color);

const draft = ref("");
const inputRef = ref(null);
const isFocused = ref(false);

const addValue = () => {
  const value = draft.value.trim();
  if (!value) return;
  if (!values.value.includes(value)) {
    emit("update:modelValue", [...values.value, value]);
  }
  draft.value = "";
  clearErrors();
};

const removeValue = (index) => {
  if (props.readOnly) return;
  const next = [...values.value];
  next.splice(index, 1);
  emit("update:modelValue", next);
};

const handleKeydown = (e) => {
  if (e.key === "Enter" || e.key === ",") {
    e.preventDefault();
    addValue();
  } else if (e.key === "Backspace" && !draft.value && values.value.length) {
    removeValue(values.value.length - 1);
  }
};

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
  if (!text) return "-";
  if (!props.highlight || !props.highlight.trim()) return text;

  const term = escapeRegExp(props.highlight.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text.toString().replace(regex, '<span class="search-highlight">$1</span>');
};

const focusInput = () => {
  inputRef.value?.focus();
};
</script>

<template>
  <div v-if="mode === 'edit' || mode === 'settings'">
    <div
      class="multivalue-field multivalue-field--edit"
      :class="{
        'multivalue-field--error': showError,
        'multivalue-field--readonly': readOnly,
      }"
      :style="{ '--module-color': color }"
      @click="!readOnly && focusInput()"
    >
      <ul class="multivalue-capsules">
        <li v-for="(value, index) in values" :key="value + index" class="multivalue-capsule">
          <span class="multivalue-capsule__label">{{ value }}</span>
          <i
            v-if="!readOnly"
            class="fa-solid fa-xmark multivalue-capsule__remove"
            @click.stop="removeValue(index)"
          ></i>
        </li>
        <li class="multivalue-input-wrapper">
          <input
            v-if="!readOnly"
            ref="inputRef"
            v-model="draft"
            type="text"
            class="multivalue-input"
            :placeholder="isFocused ? placeholder || t('fields.multivalue_placeholder') : ''"
            @keydown="handleKeydown"
            @focus="isFocused = true"
            @blur="isFocused = false; addValue()"
          />
        </li>
      </ul>
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </div>
  </div>

  <div v-else-if="mode === 'detail'">
    <div
      class="multivalue-field multivalue-field--detail display-field"
      :class="{ 'multivalue-field--empty': !values.length }"
      :style="{ '--module-color': color }"
    >
      <ul v-if="values.length" class="multivalue-capsules">
        <li v-for="(value, index) in values" :key="value + index" class="multivalue-capsule">
          <span class="multivalue-capsule__label">{{ value }}</span>
        </li>
      </ul>
      <span v-else>—</span>
    </div>
  </div>

  <div v-else-if="mode === 'table' || mode === 'related-panel' || mode === 'linkingPanel'">
    <div
      class="multivalue-field multivalue-field--table"
      :style="{ '--module-color': color }"
    >
      <ul v-if="values.length" class="multivalue-capsules">
        <li
          v-for="(value, index) in values"
          :key="value + index"
          class="multivalue-capsule multivalue-capsule--sm"
        >
          <span v-if="searchable" v-html="highlightMatch(value)"></span>
          <span v-else class="multivalue-capsule__label">{{ value }}</span>
        </li>
      </ul>
      <span v-else class="field-empty">—</span>
    </div>
  </div>
</template>
