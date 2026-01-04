<script setup>
import { Link, usePage } from "@inertiajs/vue3";
const props = defineProps({
  settingModule: Object,
  activeKey: String,
});
const baseUrl = `/settings/modules/${props.settingModule?.id}`;

const getUrl = (page) => {
  return page === "edit" ? baseUrl : `${baseUrl}/${page}`;
};
const appSettings = usePage().props.appSettings;
</script>
<template>
  <div
    class="settings__module__tabs"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': settingModule.color }
    "
  >
    <Link
      :href="getUrl('edit')"
      class="settings__module__tabs__item"
      :class="{ 'settings__module__tabs__item--active': activeKey === 'edit' }"
    >
      Module Settings
    </Link>
    <Link
      :href="getUrl('layouts')"
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'layouts',
      }"
      >Layouts</Link
    >
    <Link
      :href="getUrl('fields')"
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'fields',
      }"
      >Fields</Link
    >
    <Link
      :href="getUrl('relationships')"
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'relationship',
      }"
      >Relationships</Link
    >
  </div>
</template>
