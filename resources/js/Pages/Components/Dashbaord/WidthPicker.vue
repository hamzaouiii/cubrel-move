<script setup>
defineProps({
  modelValue: { type: Number, default: 1 },
  max:        { type: Number, default: 4 },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="wip">
    <button
      v-for="n in max"
      :key="n"
      type="button"
      class="wip__btn"
      :class="{ 'wip__btn--active': modelValue === n }"
      @click="$emit('update:modelValue', n)"
    >
      {{ n }}
    </button>
  </div>
</template>
