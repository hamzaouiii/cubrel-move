<script setup>
import { computed, getCurrentInstance } from "vue";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import ModuleSwitcher from "@/Pages/Components/Settings/ModuleSwitcher.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";

const props = defineProps({
  settingModule: Object,
  activeKey: String,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const crumbs = computed(() => [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.modules"), href: "/settings/modules" },
  { label: props.settingModule.label },
]);
</script>

<template>
  <div class="module-settings-header">
    <div class="module-settings-header__top">
      <SettingsBreadcrumb :crumbs="crumbs" />
      <ModuleSwitcher :setting-module="settingModule" :active-key="activeKey" />
    </div>
    <ModuleSettingTabs :setting-module="settingModule" :active-key="activeKey" />
  </div>
</template>
