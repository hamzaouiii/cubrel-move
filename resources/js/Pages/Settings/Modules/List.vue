<script setup>
import { computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import ModuleManager from "@/Pages/Components/Settings/Modules/ModuleManager.vue";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: [AppLayout, SettingsLayout],
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  item: Object,
  setting_modules: Object,
});
const page = usePage();
const module = computed(() => page.props.item || page.props);
const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.modules") },
];
</script>
<template>
  <Head>
    <title>{{ $t(item.label) }} - {{ $t("settings.label") }} - Cubrel</title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__items">
      <div class="settings__module__header">
        <SettingsBreadcrumb :crumbs="crumbs" />
      </div>
      <ModuleManager v-if="setting_modules" :modules="setting_modules">
      </ModuleManager>
    </div>
  </div>
</template>
