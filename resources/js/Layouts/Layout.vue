<script setup>
import Sidebar from "@/Pages/Components/Globals/Sidebar.vue";
import Topbar from "@/Pages/Components/Globals/Topbar.vue";
import Alerts from "@/Pages/Components/Globals/Alerts.vue";
import ConfirmOverlay from "@/Pages/Components/Globals/ConfirmOverlay.vue";
import { usePage } from "@inertiajs/vue3";
import { computed, provide } from "vue";

import { useAlerts } from "@/Composables/useAlerts";

const { alerts } = useAlerts();

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
</script>

<template>
  <div class="root">
    <ConfirmOverlay />
    <sidebar></sidebar>
    <Alerts :alerts="alerts" />
    <main class="root__content">
      <Topbar></Topbar>
      <slot />
    </main>
  </div>
</template>
