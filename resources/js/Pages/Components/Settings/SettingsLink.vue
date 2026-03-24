<script setup>
import { usePage, Link } from "@inertiajs/vue3";
const props = defineProps({
  color: String,
});
const appSettings = usePage().props.appSettings;
</script>

<template>
  <div class="settings__module__header" :style="{ '--module-color': color }">
    <Link href="/settings">
      <i class="fa-solid fa-arrow-left"></i>
      {{ $t("settings.back_to_settings") }}
    </Link>
  </div>
</template>
