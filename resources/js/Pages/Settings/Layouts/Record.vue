<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { getCurrentInstance } from "vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
});

const page = usePage();
const currentPath = page.url;
const appSettings = usePage().props.appSettings;

const { proxy } = getCurrentInstance();
const t = proxy.$t;
</script>

<template>
  <Head>
    <title>{{ module.label }} - {{ $t("settings.label") }}</title>
  </Head>

  <div
    class="settings"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>

    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="layouts"
      ></ModuleSettingTabs>
      <div class="settings__modules">
        <Link class="settings__modules__card" :href="currentPath + '/list'">
          <div class="settings__modules__card__icon">
            <i class="fa-solid fa-table-list"></i>
          </div>
          <span class="settings__modules__card__label">
            {{ $t("layouts.list") }}
          </span>
        </Link>

        <Link class="settings__modules__card" :href="currentPath + '/record'">
          <div class="settings__modules__card__icon">
            <i class="fa-regular fa-address-card"></i>
          </div>
          <span class="settings__modules__card__label">
            {{ $t("layouts.record_overview") }}
          </span>
        </Link>
        <Link class="settings__modules__card" :href="currentPath + '/related'">
          <div class="settings__modules__card__icon">
            <i class="fa-solid fa-diagram-predecessor"></i>
          </div>
          <span class="settings__modules__card__label">
            {{ $t("layouts.related") }}
          </span>
        </Link>
        <Link
          class="settings__modules__card"
          :href="currentPath + '/linkingPanel'"
        >
          <div class="settings__modules__card__icon">
            <i class="fa-solid fa-link"></i>
          </div>
          <span class="settings__modules__card__label">
            {{ $t("layouts.linkingPanel") }}
          </span>
        </Link>
      </div>
    </div>
  </div>
</template>
