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
</script>

<template>
  <!-- EDIT MODE DONE -->
  <div v-if="mode === 'edit'">
    <span
      class="text-field text-field--edit"
      :class="{
        'text-field--error': showError,
        'text-field--readonly': readOnly,
      }"
    >
      <input v-model="localValue" type="text" @input="clearErrors()" />
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <!-- DETAIL MODE DONE-->
  <div v-else-if="mode === 'detail' || 'table'">
    <span :class="['text-field', { 'text-field--readonly': readOnly }]">
      {{ modelValue }}
    </span>
  </div>

  <!-- TABLE MODE -->
  <div v-else-if="mode === 'table'">
    <span :title="modelValue">
      {{ modelValue || "—" }}
    </span>
  </div>
</template>
