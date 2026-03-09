<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
const props = defineProps({
  settingModule: Object,
});

const appSettings = usePage().props.appSettings;

const getLabel = (i) => {
  return props.settingModule?.label || labelMapper[i];
};

const labelMapper = {
  settings: "settings.label",
  modules: "settings.items.modules",
  dropdowns: "settings.items.dropdowns",
  modulebuilder: "settings.items.modulebuilder",
  fields: "settings.items.fields",
  layouts: "settings.items.layouts",
  list: "layouts.list",
  record: "layouts.record",
};
const isUUID = (str) => {
  const uuidRegex =
    /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
  return uuidRegex.test(str);
};
const breadcrumbs = computed(() => {
  const { url } = usePage();
  const segments = url.split("/").filter((segment) => segment);

  let currentPath = "";
  let label = "";
  return segments
    .map((segment, index) => {
      currentPath += `/${segment}`;

      if (isUUID(segment)) {
        label = props.settingModule?.label || "create";
      } else {
        label = labelMapper[segment];
      }
      if (label == undefined) return;
      return {
        label,
        path: currentPath,
        isCurrent: index === segments.length - 1,
      };
    })
    .filter((segment) => segment);
});

const isDropdown = () => {
  return props.settingModule?.slug === "dropdowns" || false;
};
</script>

<template>
  <div
    class="settings__header__title__breadcrumbs"
    :style="[{ '--primary-color': appSettings.primary_color }]"
  >
    <template v-for="(item, index) in breadcrumbs" :key="index">
      <span
        v-if="!item.isCurrent"
        class="settings__header__title__breadcrumbs__link"
      >
        <Link v-if="item.path" :href="item.path">{{ $t(item.label) }}</Link>
        <span v-else class="settings__header__title__breadcrumbs__current">{{
          $t(item.label)
        }}</span>
      </span>
      <span v-else class="settings__header__title__breadcrumbs__current">{{
        $t(item.label)
      }}</span>

      <span class="breadcrumb-separator" v-if="index < breadcrumbs.length - 1">
        <i class="fa-solid fa-angle-right"></i>
      </span>
    </template>
  </div>
</template>
