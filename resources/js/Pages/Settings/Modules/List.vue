<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import ModuleManager from "@/Pages/Components/Settings/Modules/ModuleManager.vue";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: AppLayout,
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
    <title>{{ $t(item.label) }} - {{ $t("settings.label") }} - Cubrel</title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__items">
      <div class="settings__module__header">
        <Link href="/settings">
          <i class="fa-solid fa-arrow-left"></i>
          {{ $t("settings.back_to_settings") }}
        </Link>
      </div>
      <ModuleManager v-if="setting_modules" :modules="setting_modules">
      </ModuleManager>
    </div>
  </div>
</template>
