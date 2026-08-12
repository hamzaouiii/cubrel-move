<script setup>
import Sidebar from "@/Pages/Components/Globals/Sidebar.vue";
import Topbar from "@/Pages/Components/Globals/Topbar.vue";
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import NotificationToasts from "@/Pages/Components/Globals/NotificationToasts.vue";
import ConfirmOverlay from "@/Pages/Components/Globals/ConfirmOverlay.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide, ref, onMounted, onUnmounted } from "vue";
import { echo } from "@laravel/echo-vue";
import ImpersonationBanner from "@/Pages/Components/Globals/ImpersonationBanner.vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useFlashToasts } from "@/Composables/useFlashToasts";
import { useKeepAlive } from "@/Composables/useKeepAlive";
import { useNotifications } from "@/Composables/useNotifications";
import { useLiveToasts } from "@/Composables/useLiveToasts";
import { useThemeMode } from "@/Composables/useThemeMode";

const { alerts, info, error, warning, success } = useAlerts();
useFlashToasts();
useKeepAlive();
const page = usePage();
const user = page.props.auth?.user ?? null;
const { applyLiveNotification } = useNotifications();
const { pushToast } = useLiveToasts();
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

let notificationChannel = null;

onMounted(() => {
  checkScreenSize();
  window.addEventListener("resize", checkScreenSize);

  if (user) {
    notificationChannel = echo()
      .private(`App.Models.User.${user.id}`)
      .notification((payload) => {
        applyLiveNotification(payload);
        pushToast(payload);
      });
  }
});

onUnmounted(() => {
  window.removeEventListener("resize", checkScreenSize);

  if (notificationChannel) {
    echo().leave(`App.Models.User.${user.id}`);
  }
});

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
      '--secondary-color': appSettings.secondary_color,
      '--success-color': appSettings.success_color,
    }"
    :class="{ impersonating: page.props.auth.impersonating }"
    v-if="!showSmallScreenOverlay"
  >
    <ConfirmOverlay />
    <Sidebar></Sidebar>
    <Alerts :alerts="alerts" />
    <NotificationToasts />
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
