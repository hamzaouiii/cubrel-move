<script setup>
import { computed, ref, watch } from "vue";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [String, Number, null],
    default: "",
  },
  mode: {
    type: String,
    default: "edit", // edit | detail | table
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
      class="record-layout__sections__item__layout__field__content editing-mode"
      :class="{ error: showError }"
    >
      <input v-model="localValue" type="text" @input="clearErrors()" />
      <span v-if="showError" class="error-icon-container">
        <i class="error-icon fa-solid fa-circle-exclamation"></i>
      </span>
    </span>
  </div>

  <!-- DETAIL MODE DONE-->
  <div v-else-if="mode === 'detail'">
    <span
      :class="[
        'record-layout__sections__item__layout__field__content',
        { 'view-uneditable-field': readOnly },
      ]"
    >
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
