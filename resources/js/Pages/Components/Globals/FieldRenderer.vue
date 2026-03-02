<script setup>
import { computed } from "vue";
import { fieldRegistry } from "@/Registries/fieldRegistry";

const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: null,
  mode: {
    type: String,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  moduleColor: {
    type: String,
    default: null,
  },
  hasError: {
    Type: Boolean,
  },
});

const component = computed(() => {
  return fieldRegistry[props.field.type] || fieldRegistry["text"];
});

const componentProps = computed(() => ({
  ...props.field,
  modelValue: props.modelValue,
  mode: props.mode,
  disabled: props.readOnly || props.field.disabled,
  moduleColor: props.moduleColor,
  hasError: props.hasError,
  readOnly: props.readOnly,
}));
</script>

<template>
  <component
    :is="component"
    v-bind="componentProps"
    @update:modelValue="emit('update:modelValue', $event)"
  />
</template>
