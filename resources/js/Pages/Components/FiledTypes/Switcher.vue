<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
const emit = defineEmits(["update:modelValue"]);

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: null,
  },
  options: {
    type: Array,
    required: true,
    validator: (opts) =>
      opts.length === 2 &&
      opts.every(
        (o) =>
          Object.prototype.hasOwnProperty.call(o, "label") &&
          Object.prototype.hasOwnProperty.call(o, "value"),
      ),
  },
  color: {
    type: String,
    required: false,
  },
});

const current = computed({
  get: () => props.modelValue ?? props.options[0]?.value,
  set: (val) => emit("update:modelValue", val),
});
const page = usePage();
const appSettings = page.props.appSettings;
const color = computed(() => props?.color ?? appSettings.primary_color);
</script>

<template>
  <div class="switcher" :style="{ '--primary-color': color }">
    <span
      v-for="opt in options"
      :key="opt.value"
      class="switch-option"
      :class="{ selected: current === opt.value }"
      @click="current = opt.value"
    >
      {{ opt.label }}
    </span>
  </div>
</template>
