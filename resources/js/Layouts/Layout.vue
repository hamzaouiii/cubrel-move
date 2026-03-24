<script setup>
import Sidebar from "@/Pages/Components/Globals/Sidebar.vue";
import Topbar from "@/Pages/Components/Globals/Topbar.vue";
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import ConfirmOverlay from "@/Pages/Components/Globals/ConfirmOverlay.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide } from "vue";

import { useAlerts } from "@/Composables/useAlerts";

const { alerts, info, error, warning, success } = useAlerts();

const page = usePage();
const user = page.props.auth?.user ?? null;
const csrf =
  page.props.csrf_token ??
  document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("root__content");

const appSettings = computed(() => page.props.appSettings || {});
const useModuleColors = computed(() => appSettings.value.useModuleColors);

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
    <!-- <MainOverlay></MainOverlay> -->
    <ConfirmOverlay />
    <Sidebar></Sidebar>
    <Alerts :alerts="alerts" />
    <main class="root__content">
      <Topbar></Topbar>
      <slot />
    </main>
  </div>
  <div class="unsupported-device">
    <h3><i class="fa-solid fa-skull-crossbones"></i></h3>
    <h3>{{ $t("globals.unsupported_device_title") }}</h3>
    <p>
      {{ $t("globals.unsupported_device_message") }}
    </p>
    <p>
      {{ $t("globals.unsupported_device_action_request") }}
    </p>
  </div>
</template>
