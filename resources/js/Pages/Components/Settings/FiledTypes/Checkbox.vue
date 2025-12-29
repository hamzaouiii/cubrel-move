<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const emit = defineEmits(["update:modelValue"]);
const appSettings = usePage().props.appSettings;

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  moduleColor: {
    type: String,
    required: false,
  },
});

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});
</script>

<template>
  <label
    class="checkbox"
    :style="
      moduleColor
        ? { '--module-color': moduleColor }
        : { '--module-color': appSettings.primary_color }
    "
  >
    <input type="checkbox" class="checkbox__input" v-model="value" />
    <span class="checkbox__slider"></span>
  </label>
</template>
