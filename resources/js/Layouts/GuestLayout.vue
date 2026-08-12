<script setup>
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide } from "vue";

import { useAlerts } from "@/Composables/useAlerts";
import { useFlashToasts } from "@/Composables/useFlashToasts";
import { useThemeMode } from "@/Composables/useThemeMode";

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

const themeEnabled = computed(() => appSettings.value.dark_mode_enabled == 1);
const theme = computed(() => appSettings.value.theme);
useThemeMode(themeEnabled, theme);

</script>

<template>
  <div
    class="root"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
      '--success-color': appSettings.success_color,
      '--secondary-color': appSettings.secondary_color,
    }"
  >
    <Alerts :alerts="alerts" />
    <main class="root__content">
      <slot />
    </main>
  </div>
</template>
