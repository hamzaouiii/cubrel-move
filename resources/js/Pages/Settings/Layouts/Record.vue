<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { getCurrentInstance } from "vue";
import ModuleSettingsHeader from "@/Pages/Components/Settings/ModuleSettingsHeader.vue";

defineOptions({
  layout: [AppLayout, SettingsLayout],
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
    <title>{{ module.label }} - {{ $t("settings.label") }} - Cubrel</title>
  </Head>

  <div
    class="settings"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="settings__module">
      <ModuleSettingsHeader
        :setting-module="module"
        active-key="layouts"
      ></ModuleSettingsHeader>
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
        <Link
          v-if="module.has_line_items"
          class="settings__modules__card"
          :href="currentPath + '/lineItemsSnapshot'"
        >
          <div class="settings__modules__card__icon">
            <i class="fa-solid fa-list-ol"></i>
          </div>
          <span class="settings__modules__card__label">
            {{ $t("layouts.lineItemsSnapshot") }}
          </span>
        </Link>
      </div>
    </div>
  </div>
</template>
