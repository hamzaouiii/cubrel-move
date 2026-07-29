<script setup>
import { computed } from "vue";
import SettingDropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import ExpressionBuilder from "@/Pages/Components/Settings/Transformations/ExpressionBuilder.vue";

const props = defineProps({
  modelValue: { type: Object, required: true },
  targetFields: { type: Array, default: () => [] },
  sourceFields: { type: Array, default: () => [] },
  sourceModule: { type: String, default: "" },
  allowFieldMode: { type: Boolean, default: true },
});

const emit = defineEmits(["update:modelValue", "remove"]);

const update = (key, value) => {
  emit("update:modelValue", { ...props.modelValue, [key]: value });
};

const targetFieldOptions = computed(() =>
  props.targetFields.map((f) => ({ value: f.name, label: f.label || f.name })),
);

const targetFieldMeta = computed(
  () =>
    props.targetFields.find((f) => f.name === props.modelValue.target_field) ??
    null,
);

const NUMERIC_FIELD_TYPES = [
  "currency",
  "percentage",
  "decimal",
  "integer",
  "duration",
];
const isNumericFieldType = (type) => NUMERIC_FIELD_TYPES.includes(type);
const sourceFieldOptions = computed(() =>
  props.sourceFields.map((f) => ({ value: f.name, label: f.label || f.name })),
);
const modeOptions = computed(() => {
  const options = [];
  if (props.allowFieldMode) {
    options.push({
      value: "field",
      label: "globals.transformations.options.mode_field",
    });
  }
  options.push(
    { value: "static", label: "globals.transformations.options.mode_static" },
    {
      value: "expression",
      label: "globals.transformations.options.mode_expression",
    },
  );
  return options;
});
</script>

<template>
  <div class="transformation-mapping-row">
    <div class="transformation-mapping-row__top">
      <SettingDropdownField
        class="transformation-mapping-row__target"
        :model-value="modelValue.target_field"
        :options="targetFieldOptions"
        :placeholder="
          $t('globals.transformations.placeholders.target_field_placeholder')
        "
        :searchable="true"
        @update:model-value="(v) => update('target_field', v)"
      />

      <i class="fa-solid fa-arrow-left transformation-mapping-row__arrow"></i>

      <SettingDropdownField
        class="transformation-mapping-row__mode"
        :model-value="modelValue.mode"
        :options="modeOptions"
        :searchable="false"
        @update:model-value="(v) => update('mode', v)"
      />

      <SettingDropdownField
        v-if="modelValue.mode === 'field'"
        class="transformation-mapping-row__value"
        :model-value="modelValue.source_field"
        :options="sourceFieldOptions"
        :placeholder="
          $t('globals.transformations.placeholders.source_field_placeholder')
        "
        :searchable="true"
        @update:model-value="(v) => update('source_field', v)"
      />

      <input
        v-else-if="
          modelValue.mode === 'static' &&
          isNumericFieldType(targetFieldMeta?.type)
        "
        type="number"
        step="any"
        class="transformation-mapping-row__value transformation-mapping-row__input"
        :placeholder="
          $t('globals.transformations.placeholders.value_placeholder')
        "
        :value="modelValue.value"
        @input="update('value', $event.target.value)"
      />

      <div
        v-else-if="modelValue.mode === 'static' && targetFieldMeta"
        class="transformation-mapping-row__value"
      >
        <FieldRenderer
          :field="targetFieldMeta"
          :model-value="modelValue.value"
          mode="settings"
          @update:model-value="(v) => update('value', v)"
        />
      </div>

      <input
        v-else-if="modelValue.mode === 'static'"
        type="text"
        class="transformation-mapping-row__value transformation-mapping-row__input"
        :placeholder="
          $t('globals.transformations.placeholders.value_placeholder')
        "
        :value="modelValue.value"
        @input="update('value', $event.target.value)"
      />

      <button
        type="button"
        class="transformation-mapping-row__remove"
        @click="$emit('remove')"
        :title="$t('globals.transformations.buttons.remove_row')"
      >
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>

    <ExpressionBuilder
      v-if="modelValue.mode === 'expression'"
      :model-value="modelValue.expression ?? []"
      :source-fields="sourceFields"
      :source-module="sourceModule"
      @update:model-value="(v) => update('expression', v)"
    />
  </div>
</template>
