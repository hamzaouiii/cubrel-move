<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, reactive } from "vue";

defineEmits(["collapse"]);

const page = usePage();
const nav = computed(() => page.props.settingsNav || { categories: {}, modules: [] });
const currentUrl = computed(() => page.url);

const expanded = reactive({});

function isModulesItem(item) {
  return item.slug === "modules";
}

function isActiveItem(item) {
  return currentUrl.value === item.path || currentUrl.value.startsWith(`${item.path}/`);
}

function isActiveModuleGroup(group) {
  return !!group.items?.modules && currentUrl.value.startsWith("/settings/modules/");
}

function moduleUrl(m) {
  return `/settings/modules/${m.id}/module-settings`;
}

function isActiveModule(m) {
  return currentUrl.value.startsWith(`/settings/modules/${m.id}`);
}

function isExpanded(groupKey, group) {
  if (groupKey in expanded) {
    return expanded[groupKey];
  }
  return isActiveModuleGroup(group);
}

function toggle(groupKey, group) {
  expanded[groupKey] = !isExpanded(groupKey, group);
}
</script>

<template>
  <nav class="settings-rail">
    <button
      type="button"
      class="settings-rail__collapser"
      :aria-label="$t('settings.collapse_menu')"
      @click="$emit('collapse')"
    >
      <i class="fa-solid fa-angles-left"></i>
      <span>{{ $t("settings.collapse_menu") }}</span>
    </button>

    <div v-for="(group, groupKey) in nav.categories" :key="groupKey" class="settings-rail__group">
      <div class="settings-rail__group__label">{{ $t(group.label) }}</div>

      <template v-for="(item, itemKey) in group.items" :key="itemKey">
        <div v-if="isModulesItem(item)" class="settings-rail__module-group">
          <button
            type="button"
            class="settings-rail__item settings-rail__item--expandable"
            :class="{ 'settings-rail__item--active': isActiveItem(item) }"
            @click="toggle(groupKey, group)"
          >
            <i :class="item.icon"></i>
            <span class="settings-rail__item__label">{{ $t(item.label) }}</span>
            <i
              class="fa-solid fa-chevron-right settings-rail__item__chevron"
              :class="{ 'settings-rail__item__chevron--open': isExpanded(groupKey, group) }"
            ></i>
          </button>
          <div v-show="isExpanded(groupKey, group)" class="settings-rail__submenu">
            <Link
              v-for="m in nav.modules"
              :key="m.id"
              :href="moduleUrl(m)"
              class="settings-rail__submenu__item"
              :class="{ 'settings-rail__submenu__item--active': isActiveModule(m) }"
              :style="{ '--module-color': m.color }"
            >
              <i :class="['fa-solid', m.icon]"></i>
              <span>{{ m.label }}</span>
            </Link>
          </div>
        </div>
        <Link
          v-else
          :href="item.path"
          class="settings-rail__item"
          :class="{ 'settings-rail__item--active': isActiveItem(item) }"
        >
          <i :class="item.icon"></i>
          <span class="settings-rail__item__label">{{ $t(item.label) }}</span>
        </Link>
      </template>
    </div>
  </nav>
</template>
