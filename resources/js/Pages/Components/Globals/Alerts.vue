<script setup>
import { onUnmounted, ref, computed, watch } from "vue";

const timers = ref({});

const iconMap = {
  error: "fa-solid fa-circle-exclamation",
  success: "fa-solid fa-circle-check",
  warning: "fa-solid fa-triangle-exclamation",
  info: "fa-solid fa-circle-info",
};

const props = defineProps({
  alerts: {
    type: Array,
    default: () => [],
  },
});

const normalizedAlerts = computed(() =>
  props.alerts.map((a) => ({
    id: a.id ?? null,
    message: a.message ?? "",
    type: a.type ?? "info",
    dismissible: a.dismissible ?? false,
    progressable: a.progressable ?? false,
    timeout: a.timeout ?? 5000,
  })),
);

const closeAlert = (id) => {
  const index = props.alerts.findIndex((a) => a.id === id);
  if (index !== -1) {
    props.alerts.splice(index, 1);
  }

  if (timers.value[id]) {
    clearTimeout(timers.value[id]);
    delete timers.value[id];
  }
};

watch(
  () => normalizedAlerts.value,
  (alerts) => {
    alerts.forEach((alert) => {
      if (alert.progressable && alert.timeout && !timers.value[alert.id]) {
        timers.value[alert.id] = setTimeout(() => {
          closeAlert(alert.id);
        }, alert.timeout);
      }
    });
  },
  { immediate: true, deep: true },
);

onUnmounted(() => {
  Object.values(timers.value).forEach(clearTimeout);
});
</script>
<template>
  <div class="alerts">
    <div
      v-for="alert in normalizedAlerts"
      :key="alert.id"
      class="alerts__item"
      :class="`alerts__item--${alert.type}`"
    >
      <i :class="iconMap[alert.type]"></i>
      <div class="alerts__item__message">
        {{ alert.message }}
      </div>

      <div class="alerts__item__close">
        <span v-if="alert.dismissible !== false" @click="closeAlert(alert.id)">
          <i class="fa-solid fa-times"></i>
        </span>
      </div>
      <div
        v-if="alert.progressable"
        class="alerts__item__progress"
        :style="{ animationDuration: alert.timeout + 'ms' }"
      />
    </div>
  </div>
</template>
