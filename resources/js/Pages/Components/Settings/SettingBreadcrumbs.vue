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
  <nav
    class="settings__breadcrumbs"
    :style="{ '--primary-color': appSettings.primary_color }"
    aria-label="Breadcrumb"
  >
    <div class="breadcrumb-item">
      <Link href="/settings" class="breadcrumb-link">
        {{ $t("settings.label") }}
      </Link>
    </div>

    <span class="breadcrumb-separator">
      <i class="fa-solid fa-angle-right"></i>
    </span>

    <div class="breadcrumb-item breadcrumb-item--current">
      {{ $t(settingItem.label) }}
    </div>
  </nav>
</template>
