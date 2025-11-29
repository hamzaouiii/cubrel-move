<script setup>
import { ref, watch } from 'vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: {
    type: String,
    default: 'en',
  },
})

const current = ref(props.modelValue)

watch(current, (val) => {
  emit('update:modelValue', val)
})
</script>

<template>
  <div class="switcher">
    <span
      class="switch-option"
      :class="{ selected: current === 'en' }"
      @click="current = 'en'"
    >
      EN
    </span>

    <span
      class="switch-option"
      :class="{ selected: current === 'de' }"
      @click="current = 'de'"
    >
      DE
    </span>
  </div>
</template>
