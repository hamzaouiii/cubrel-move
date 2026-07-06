<script setup>
import Sidebar from "@/Pages/Components/Globals/Sidebar.vue";
import Topbar from "@/Pages/Components/Globals/Topbar.vue";
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import ConfirmOverlay from "@/Pages/Components/Globals/ConfirmOverlay.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide, ref, onMounted, onUnmounted } from "vue";
import ImpersonationBanner from "@/Pages/Components/Globals/ImpersonationBanner.vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useFlashToasts } from "@/Composables/useFlashToasts";
import { useKeepAlive } from "@/Composables/useKeepAlive";

const { alerts, info, error, warning, success } = useAlerts();
useFlashToasts();
useKeepAlive();
const page = usePage();
const user = page.props.auth?.user ?? null;
const csrf =
  page.props.csrf_token ??
  document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("root__content");

const SMALL_SCREEN_BREAKPOINT = 1024;
const SESSION_KEY = "cubrel_dismissed_small_screen";

const showSmallScreenOverlay = ref(false);

function checkScreenSize() {
  const dismissed = sessionStorage.getItem(SESSION_KEY) === "true";
  const isSmall = window.innerWidth <= SMALL_SCREEN_BREAKPOINT;
  showSmallScreenOverlay.value = isSmall && !dismissed;
}

function dismissSmallScreenWarning() {
  sessionStorage.setItem(SESSION_KEY, "true");
  showSmallScreenOverlay.value = false;
}

onMounted(() => {
  checkScreenSize();
  window.addEventListener("resize", checkScreenSize);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkScreenSize);
});

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
    :class="{ impersonating: page.props.auth.impersonating }"
    v-if="!showSmallScreenOverlay"
  >
    <ConfirmOverlay />
    <Sidebar></Sidebar>
    <Alerts :alerts="alerts" />
    <ImpersonationBanner v-if="page.props.auth.impersonating" />
    <main class="root__content">
      <Topbar></Topbar>
      <slot />
    </main>
  </div>
  <div class="unsupported-device" v-else>
    <h3><i class="fa-solid fa-skull-crossbones"></i></h3>
    <h3>{{ $t("globals.unsupported_device_title") }}</h3>
    <p>{{ $t("globals.unsupported_device_message") }}</p>
    <p>{{ $t("globals.unsupported_device_action_request") }}</p>
    <button
      class="unsupported-device__dismiss"
      @click="dismissSmallScreenWarning"
    >
      {{ $t("globals.unsupported_device_dismiss") }}
    </button>
  </div>
</template>
