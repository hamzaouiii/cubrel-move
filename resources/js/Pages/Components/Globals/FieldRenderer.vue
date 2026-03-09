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
  related_field: Object,
  highlight: String,
  searchable: Boolean,
  sortable: Boolean,
});

const component = computed(() => {
  return fieldRegistry[props.field?.type] || fieldRegistry["text"];
});
const dropdown_list = computed(() => {
  return props?.related_field?.dropdown_list || null;
});
const componentProps = computed(() => ({
  ...props.field,
  ...(dropdown_list.value && { dropdown_list: dropdown_list.value }),

  modelValue: props.modelValue,
  mode: props.mode,
  moduleColor: props.moduleColor,
  hasError: props.hasError,
  readOnly: props.readOnly,
  highlight: props?.highlight || null,
}));
</script>

<template>
  <component
    :is="component"
    v-bind="componentProps"
    @update:modelValue="emit('update:modelValue', $event)"
  />
</template>
