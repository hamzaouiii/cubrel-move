<script setup>
import { Link, usePage } from "@inertiajs/vue3";
const props = defineProps({
  settingModule: Object,
});

const appSettings = usePage().props.appSettings;
</script>
<template>
  <div
    class="settings__header__title__breadcrumbs"
    :style="[{ '--primary-color': appSettings.primary_color }]"
  >
    <h5>
      <Link href="/settings">{{ $t("settings.label") }} </Link>
    </h5>
    <span><i class="fa-solid fa-angle-right"></i></span>
    <h5>
      <Link href="/settings/modules">{{ $t("settings.modules.label") }} </Link>
    </h5>
    <span><i class="fa-solid fa-angle-right"></i></span>
    <h6>{{ settingModule.label }}</h6>
  </div>
</template>
