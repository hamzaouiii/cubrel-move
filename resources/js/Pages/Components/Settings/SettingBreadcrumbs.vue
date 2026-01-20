<script setup>
import { Link, usePage } from "@inertiajs/vue3";
const props = defineProps({
  settingItem: {
    type: Object,
    required: false,
  },
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
      <h5>
        <Link href="/settings">{{ $t("settings.label") }} </Link>
      </h5>
      <span><i class="fa-solid fa-angle-right"></i></span>
      <h5>
        {{ $t(settingItem.label) }}
      </h5>
    </div>
  </div>
</template>
