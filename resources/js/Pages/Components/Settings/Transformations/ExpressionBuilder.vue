<script setup>
import { computed, ref, watch } from "vue";
import axios from "axios";
import SettingDropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  sourceFields: { type: Array, default: () => [] },
  sourceModule: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const segments = computed(() =>
  props.modelValue.length ? props.modelValue : [{ type: "text", value: "" }],
);

const segmentTypeOptions = [
  { value: "text", label: "globals.transformations.options.segment_text" },
  { value: "field", label: "globals.transformations.options.segment_field" },
  { value: "helper", label: "globals.transformations.options.segment_helper" },
];

const helperOptions = [
  { value: "today", label: "globals.transformations.options.helper_today" },
  {
    value: "current_user",
    label: "globals.transformations.options.helper_current_user",
  },
  { value: "uuid", label: "globals.transformations.options.helper_uuid" },
];

const fieldOptions = computed(() =>
  props.sourceFields.map((f) => ({ value: f.name, label: f.label })),
);

const updateSegment = (index, patch) => {
  const next = segments.value.map((segment, i) =>
    i === index ? { ...segment, ...patch } : segment,
  );
  emit("update:modelValue", next);
};

const addSegment = () => {
  emit("update:modelValue", [...segments.value, { type: "text", value: "" }]);
};

const removeSegment = (index) => {
  const next = segments.value.filter((_, i) => i !== index);
  emit("update:modelValue", next.length ? next : [{ type: "text", value: "" }]);
};

const expressionError = ref(null);
let debounceHandle = null;

watch(
  () => JSON.stringify(segments.value),
  () => {
    expressionError.value = null;
    clearTimeout(debounceHandle);
    debounceHandle = setTimeout(checkExpression, 500);
  },
);

const checkExpression = async () => {
  const hasContent = segments.value.some(
    (s) => (s.value ?? "").toString().trim() !== "",
  );
  if (!hasContent) return;

  try {
    await axios.post("/settings/transformations/expressions/validate", {
      expression: segments.value,
      source_module: props.sourceModule,
    });
  } catch (e) {
    expressionError.value = e.response?.data?.error ?? "Invalid expression.";
  }
};
</script>

<template>
  <div class="expression-builder">
    <div
      v-for="(segment, i) in segments"
      :key="i"
      class="expression-builder__segment"
    >
      <SettingDropdownField
        class="expression-builder__type"
        :model-value="segment.type"
        :options="segmentTypeOptions"
        :searchable="false"
        @update:model-value="(v) => updateSegment(i, { type: v, value: '' })"
      />

      <input
        v-if="segment.type === 'text'"
        type="text"
        class="expression-builder__value expression-builder__value--text"
        :placeholder="
          $t('globals.transformations.placeholders.segment_text_placeholder')
        "
        :value="segment.value"
        @input="updateSegment(i, { value: $event.target.value })"
      />

      <SettingDropdownField
        v-else-if="segment.type === 'field'"
        class="expression-builder__value"
        :model-value="segment.value"
        :options="fieldOptions"
        :placeholder="
          $t('globals.transformations.placeholders.source_field_placeholder')
        "
        @update:model-value="(v) => updateSegment(i, { value: v })"
      />

      <SettingDropdownField
        v-else
        class="expression-builder__value"
        :model-value="segment.value"
        :options="helperOptions"
        :searchable="false"
        @update:model-value="(v) => updateSegment(i, { value: v })"
      />

      <button
        type="button"
        class="expression-builder__remove"
        :disabled="segments.length === 1"
        @click="removeSegment(i)"
      >
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>

    <button type="button" class="expression-builder__add" @click="addSegment">
      <i class="fa-solid fa-plus"></i>
      {{ $t("globals.transformations.buttons.add_segment_btn") }}
    </button>

    <span v-if="expressionError" class="expression-builder__error">
      {{ expressionError }}
    </span>
  </div>
</template>
