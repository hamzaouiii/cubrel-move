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
  related_label: {
    type: String,
    default: null,
  },
});

const related_label = computed(() => {
  return props.related_label ?? null;
});

const component = computed(() => {
  return (
    fieldRegistry[props.field?.type]?.component ||
    fieldRegistry["text"].component
  );
});
const dropdown_list = computed(() => {
  return props?.related_field?.dropdown_list || null;
});

const related_module = computed(() => {
  return props.field?.related_module || null;
});
const componentProps = computed(() => ({
  ...props.field,
  ...(dropdown_list.value && { dropdown_list: dropdown_list.value }),
  ...(related_module.value && { related_module: related_module.value }),
  ...(related_label.value && { related_label: related_label.value }),
  modelValue: props.modelValue,
  mode: props.mode,
  moduleColor: props.moduleColor,
  hasError: props.hasError,
  readOnly: props.readOnly,
  highlight: props?.highlight || null,
  errorMsg: props?.errorMsg || null,
  searchable: props?.searchable || null,
}));
</script>

<template>
  <component
    :is="component"
    v-bind="componentProps"
    @update:modelValue="emit('update:modelValue', $event)"
  />
</template>
