<script setup>
defineProps({
  icon: { type: String, required: true },
  iconBg: { type: String, default: '#e8f5e9' },
  iconColor: { type: String, default: '#2e7d32' },
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  change: { type: String, default: null },
  changeType: { type: String, default: 'up' },
})
</script>

<template>
  <div class="stat-card">
    <div class="stat-card__icon" :style="{ background: iconBg, color: iconColor }">
      <i :class="icon"></i>
    </div>
    <div class="stat-card__content">
      <span class="stat-card__label">{{ label }}</span>
      <span class="stat-card__value">
        {{ typeof value === 'number' ? value.toLocaleString() : value }}
      </span>
      <span v-if="change" :class="['stat-card__change', `stat-card__change--${changeType}`]">
        <i :class="changeType === 'up' ? 'fa-solid fa-arrow-up' : 'fa-solid fa-arrow-down'"></i>
        {{ change }}
      </span>
    </div>
  </div>
</template>
