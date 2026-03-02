<script setup>
import { computed, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";

const emit = defineEmits(["update:modelValue"]);
const appSettings = usePage().props.appSettings;

const props = defineProps({
  modelValue: {
    type: [Boolean, Number, String],
    default: false,
    validator: (value) => {
      return (
        typeof value === "boolean" ||
        value === 0 ||
        value === 1 ||
        value === "0" ||
        value === "1"
      );
    },
  },
  moduleColor: {
    type: String,
    required: false,
  },
  display: {
    type: Boolean,
    default: false,
  },
});

const value = computed({
  get: () => {
    if (
      props.modelValue === true ||
      props.modelValue === 1 ||
      props.modelValue === "1"
    ) {
      return true;
    }
    return false;
  },
  set: (val) => {
    if (typeof props.modelValue === "number") {
      emit("update:modelValue", val ? 1 : 0);
    } else if (typeof props.modelValue === "string") {
      emit("update:modelValue", val ? "1" : "0");
    } else {
      emit("update:modelValue", val);
    }
  },
});
</script>

<template>
  <!-- EDIT MODE -->
  <label
    v-if="!display"
    class="checkbox"
    :style="{
      '--module-color': moduleColor ? moduleColor : appSettings.primary_color,
    }"
  >
    <input type="checkbox" class="checkbox__input" v-model="value" />
    <span class="checkbox__slider"></span>
  </label>

  <!-- DETAILS MODE -->
  <div
    v-else
    class="checkbox-display"
    :class="{ 'checkbox-display--active': value }"
    :style="{
      '--module-color': moduleColor ? moduleColor : appSettings.primary_color,
    }"
  >
    <i class="fa-solid" :class="value ? 'fa-check' : 'fa-xmark'"></i>
    <span>
      {{ value ? $t("fields.checkbox_yes") : $t("fields.checkbox_no") }}
    </span>
  </div>
</template>
