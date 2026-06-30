<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
  instance: { type: Object, required: true },
});

const state = ref("loading"); // 'loading' | 'loaded' | 'error'
const value = ref(null);

async function load() {
  state.value = "loading";
  try {
    const { data } = await axios.post("/dashboard/widget-data", {
      type: props.instance.type,
      config: props.instance.config,
    });
    value.value = data.value;
    state.value = "loaded";
  } catch {
    state.value = "error";
  }
}

onMounted(load);
defineExpose({ load });

function formatted() {
  if (value.value === null) return "—";
  if (Number.isInteger(value.value)) return value.value.toLocaleString();
  return Number(value.value).toLocaleString(undefined, {
    maximumFractionDigits: 2,
  });
}
</script>

<template>
  <div class="metric-card">
    <div
      class="metric-card__icon"
      :style="{
        background: instance.config.iconBg ?? '#e8f5e9',
        color: instance.config.iconColor ?? '#2e7d32',
      }"
    >
      <i :class="instance.config.icon ?? 'fa-solid fa-chart-simple'"></i>
    </div>
    <div class="metric-card__content">
      <span class="metric-card__label">{{
        instance.config.label || instance.config.module
      }}</span>

      <span
        v-if="state === 'loading'"
        class="metric-card__value metric-card__value--loading"
      >
        <i class="fa-solid fa-atom fa-spin"></i>
      </span>
      <span
        v-else-if="state === 'error'"
        class="metric-card__value metric-card__value--error"
        >—</span
      >
      <span v-else class="metric-card__value">{{ formatted() }}</span>
    </div>
  </div>
</template>
