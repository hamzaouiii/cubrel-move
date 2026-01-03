<script setup>
import { Link } from "@inertiajs/vue3";
const props = defineProps({
  settingModule: Object,
  activeKey: String,
});
const baseUrl = `/settings/modules/${props.settingModule?.id}`;

const getUrl = (page) => {
  return page === "edit" ? baseUrl : `${baseUrl}/${page}`;
};
</script>
<template>
  <div class="settings__module__tabs">
    <Link
      :href="getUrl('edit')"
      class="settings__module__tabs__item"
      :class="{ 'settings__module__tabs__item--active': activeKey === 'edit' }"
    >
      Module Settings
    </Link>
    <Link :href="getUrl('layouts')" class="settings__module__tabs__item"
      >Layouts</Link
    >
    <Link :href="getUrl('fields')" class="settings__module__tabs__item"
      >Fields</Link
    >
    <Link :href="getUrl('relationships')" class="settings__module__tabs__item"
      >Relationships</Link
    >
  </div>
</template>
