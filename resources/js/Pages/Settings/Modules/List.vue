<script setup>
import { computed } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import ModuleManager from "@/Pages/Components/Settings/ModuleManager.vue";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
  setting_modules: Object,
});

const page = usePage();
const module = computed(() => page.props.item || page.props);
const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});
</script>
<template>
  <Head>
    <title>{{ item.label }} - {{ $t("settings.label") }}</title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <div class="settings__header__title__breadcrumbs">
          <h5>
            <Link href="/settings">{{ $t("settings.label") }}</Link>
          </h5>
          <span><i class="fa-solid fa-angle-right"></i></span>
          <h6>{{ item.label }}</h6>
        </div>
      </div>
      <div class="settings__header__action">
        <Link class="settings__header__action__create" :href="createUrl">
          {{ $t("settings.create_new_module") }}</Link
        >
      </div>
    </div>
    <div class="settings__items">
      <ModuleManager v-if="setting_modules" :modules="setting_modules">
      </ModuleManager>
    </div>
  </div>
</template>
