<script setup>
import { onMounted, onUnmounted, ref, computed } from "vue";

const timers = ref({});

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
    duration: a.duration ?? 5000,
  }))
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
</script>
<template>
  <div class="alerts">
    <div
      v-for="alert in normalizedAlerts"
      :key="alert.id"
      class="alerts__item"
      :class="`alerts__item--${alert.type}`"
    >
      <div class="alerts__item__message">
        <span>
          {{ alert.message }}
        </span>
      </div>
      <div class="alerts__item__close">
        <span v-if="alert.dismissible !== false" @click="closeAlert(alert.id)">
          <i class="fa-solid fa-times"></i>
        </span>
      </div>
    </div>
  </div>
</template>
