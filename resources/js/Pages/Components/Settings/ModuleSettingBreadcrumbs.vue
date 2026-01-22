<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
  settingModule: Object,
});

const appSettings = usePage().props.appSettings;
const currentLocation = usePage()?.url;

const isDropdown = () => {
  return props.settingModule?.slug === "dropdowns" || false;
};

// Determine which breadcrumb items to show
const getBreadcrumbItems = () => {
  const items = [];

  // Always start with Settings
  items.push({
    label: "settings.label",
    href: "/settings",
    isCurrent: false,
  });

  if (isDropdown()) {
    // For dropdowns: Settings -> Current Module
    if (currentLocation !== "/settings/dropdowns") {
      items.push({
        label: props.settingModule.label,
        href: "settings/dropdowns",
        isCurrent: false,
      });
      items.push({
        label: "settings.dropdown.edit",
        href: null,
        isCurrent: true,
      });
    } else {
      items.push({
        label: props.settingModule.label,
        href: null,
        isCurrent: true,
      });
    }
  } else if (props.settingModule) {
    // For other modules: Settings -> Modules -> Current Module
    items.push({
      label: "settings.modules.label",
      href: "/settings/modules",
      isCurrent: false,
    });
    items.push({
      label: props.settingModule.label,
      href: null,
      isCurrent: true,
    });
  } else {
    // Default: Settings -> Modules (if not already on modules page)
    if (currentLocation !== "/settings/modules") {
      items.push({
        label: "settings.modules.label",
        href: "/settings/modules",
        isCurrent: false,
      });
    } else {
      items[items.length - 1].isCurrent = true;
    }
  }

  return items;
};

const breadcrumbItems = getBreadcrumbItems();
</script>

<template>
  <div
    class="settings__header__title__breadcrumbs"
    :style="[{ '--primary-color': appSettings.primary_color }]"
  >
    <template v-for="(item, index) in breadcrumbItems" :key="index">
      <h5 v-if="!item.isCurrent">
        <Link v-if="item.href" :href="item.href">{{ $t(item.label) }}</Link>
        <span v-else>{{ $t(item.label) }}</span>
      </h5>
      <h6 v-else>{{ $t(item.label) }}</h6>

      <span v-if="index < breadcrumbItems.length - 1">
        <i class="fa-solid fa-angle-right"></i>
      </span>
    </template>
  </div>
</template>
