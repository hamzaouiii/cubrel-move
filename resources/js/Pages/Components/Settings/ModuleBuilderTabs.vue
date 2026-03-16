<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
const props = defineProps({
  settingModule: Object,
  activeKey: String,
  color: String,
});

// Define the custom event
const emit = defineEmits(["update:activeKey"]);

const appSettings = usePage().props.appSettings;

const setActive = (key) => {
  emit("update:activeKey", key);
};

const moduleColor = computed(() =>
  appSettings.use_individual_module_colors
    ? props.color
    : appSettings.primary_color,
);
</script>

<template>
  <div
    class="settings__module__tabs"
    :style="{ '--module-color': moduleColor }"
  >
    <div
      class="settings__module__tabs__item"
      :class="{ 'settings__module__tabs__item--active': activeKey === 'edit' }"
      @click="setActive('edit')"
    >
      {{ $t("settings.tabs.module_settings") }}
    </div>

    <div
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'layouts',
      }"
      @click="setActive('layouts')"
    >
      {{ $t("settings.tabs.layouts") }}
    </div>

    <div
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'fields',
      }"
      @click="setActive('fields')"
    >
      {{ $t("settings.tabs.fields") }}
    </div>

    <div
      class="settings__module__tabs__item"
      :class="{
        'settings__module__tabs__item--active': activeKey === 'relationships',
      }"
      @click="setActive('relationships')"
    >
      {{ $t("settings.tabs.relationships") || "Relationships" }}
    </div>
  </div>
</template>

<style scoped>
/* Ensure the cursor changes to pointer since they are now clickable divs */
.settings__module__tabs__item {
  cursor: pointer;
  user-select: none;
}
</style>
