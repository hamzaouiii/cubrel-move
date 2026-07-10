<script setup>
const DEFAULT_COLOR = "#374151";
const DEFAULT_BG_COLOR = "#e5e7eb";

defineProps({
  label: String,
  color: String,
  bgColor: String,
  icon: String,
});
</script>

<template>
  <span
    class="status-badge status-badge--pill"
    :style="{
      color: color || DEFAULT_COLOR,
      backgroundColor: bgColor || DEFAULT_BG_COLOR,
    }"
  >
    <i v-if="icon" :class="[icon, 'status-icon']"></i>
    <span>{{ $t(label) || '—' }}</span>
  </span>
</template>
