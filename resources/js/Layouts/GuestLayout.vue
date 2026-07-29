<script setup>
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide } from "vue";

import { useAlerts } from "@/Composables/useAlerts";
import { useFlashToasts } from "@/Composables/useFlashToasts";

const { alerts, info, error, warning, success } = useAlerts();
useFlashToasts();

const page = usePage();
const user = page.props.auth?.user ?? null;
const csrf =
  page.props.csrf_token ??
  document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("root__content");

const appSettings = computed(() => page.props.appSettings || {});
const useModuleColors = computed(() => appSettings.value.useModuleColors);
document.documentElement.style.setProperty(
  "--primary-color",
  appSettings.value?.primary_color || "#3498db",
);
provide("useModuleColors", useModuleColors);
// test alerts
// info("Operation completed successfully", { timeout: 0 });
// error("Failed to connect to the database", { timeout: 0 });
// warning("Disk space is running low", { timeout: 0, progressable: true });
// success("Your changes have been saved", { timeout: 0 });
</script>

<template>
  <div
    class="root"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
      '--secondary-color': appSettings.secondary_color,
    }"
  >
    <Alerts :alerts="alerts" />
    <main class="root__content">
      <slot />
    </main>
  </div>
</template>
