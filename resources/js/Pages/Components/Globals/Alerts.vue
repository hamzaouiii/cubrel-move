<script setup>
import { onUnmounted, ref, computed, watch } from "vue";

const timers = ref({});

const iconMap = {
    error: "fa-solid fa-circle-xmark",
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
        action: a.action ?? null,
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
            if (
                alert.progressable &&
                alert.timeout &&
                !timers.value[alert.id]
            ) {
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
            class="alert"
            :class="`alert--${alert.type}`"
        >
            <div class="alert__icon">
                <i :class="iconMap[alert.type]"></i>
            </div>

            <div class="alert__content">
                <p class="alert__message">{{ alert.message }}</p>
                <button
                    v-if="alert.action"
                    class="alert__action"
                    @click="alert.action.onClick"
                >
                    {{ alert.action.label }}
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

            <button
                v-if="alert.dismissible !== false"
                class="alert__dismiss"
                @click="closeAlert(alert.id)"
                aria-label="Dismiss alert"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div
                v-if="alert.progressable"
                class="alert__progress"
                :style="{ animationDuration: alert.timeout + 'ms' }"
            />
        </div>
    </div>
</template>
