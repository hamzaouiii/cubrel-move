<script setup>
import { computed } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
  list: Array,
});
const page = usePage();
const currentUrl = computed(() => {
  return page.url;
});
</script>
<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="item"
        ></ModuleSettingBreadcrumbs>
      </div>
      <div class="settings__header__action">
        <Link class="settings__header__action__create" :href="createUrl">
          {{ $t("settings.dropdown.create") }}</Link
        >
      </div>
    </div>
    <div class="settings__dropdown">
      <ul class="settings__dropdown__list">
        <li class="settings__dropdown__list__item" v-for="item in list">
          <Link :href="currentUrl + '/' + item.key"> {{ item.key }}</Link>
        </li>
      </ul>
    </div>
  </div>
</template>
