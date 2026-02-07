<script setup>
import { Link, usePage } from "@inertiajs/vue3";
const props = defineProps({
  settingModule: Object,
  current: String,
});

const appSettings = usePage().props.appSettings;
const currentLocation = usePage()?.url;
</script>
<template>
  <div>
    <div
      class="settings__header__title__breadcrumbs"
      :style="[{ '--primary-color': appSettings.primary_color }]"
    >
      <span class="settings__header__title__breadcrumbs__link">
        <Link href="/settings">{{ $t("settings.label") }} </Link>
      </span>
      <span><i class="fa-solid fa-angle-right"></i></span>
      <span v-if="current" class="settings__header__title__breadcrumbs__link">
        <Link href="/settings/dropdowns">{{ $t(settingModule.label) }} </Link>
      </span>
      <span v-else class="settings__header__title__breadcrumbs__current"
        >{{ $t(settingModule.label) }}
      </span>
      <span v-if="current"><i class="fa-solid fa-angle-right"></i></span>
      <span
        v-if="current"
        class="settings__header__title__breadcrumbs__current"
        >{{ current }}</span
      >
    </div>
  </div>
</template>
