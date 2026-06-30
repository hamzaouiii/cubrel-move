<script setup>
import { computed } from 'vue'
import ColorPicker from '@/Pages/Components/FiledTypes/ColorPicker.vue'
import { CHART_PALETTE } from './dashboardUi.js'

const props = defineProps({
  modelValue: { type: Array, default: null }, // null = default palette
})

const emit = defineEmits(['update:modelValue'])

const palette = computed(() => props.modelValue ?? CHART_PALETTE)
const isCustom = computed(() => props.modelValue !== null)

function updateColor(i, color) {
  const next = [...palette.value]
  next[i] = color
  emit('update:modelValue', next)
}

function addColor() {
  emit('update:modelValue', [...palette.value, '#3b8bff'])
}

function removeColor(i) {
  const next = [...palette.value]
  next.splice(i, 1)
  emit('update:modelValue', next.length ? next : null)
}

function reset() {
  emit('update:modelValue', null)
}
</script>

<template>
  <div class="pe">
    <div class="pe__swatches">
      <div v-for="(color, i) in palette" :key="i" class="pe__item">
        <ColorPicker :model-value="color" @update:modelValue="updateColor(i, $event)" />
        <button
          v-if="palette.length > 1"
          type="button"
          class="pe__remove"
          @click="removeColor(i)"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <button type="button" class="pe__add" :title="$t('globals.dashboard.palette_reset')" @click="addColor">
        <i class="fa-solid fa-plus"></i>
      </button>
    </div>

    <button v-if="isCustom" type="button" class="pe__reset" @click="reset">
      {{ $t('globals.dashboard.palette_reset') }}
    </button>
  </div>
</template>
