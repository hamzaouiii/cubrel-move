<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import Layout from "@/Layouts/Layout.vue";
import { ref, computed, getCurrentInstance } from "vue";

defineOptions({
  layout: Layout,
});

const pageProps = defineProps({
  settings: Object,
});
const appSettings = usePage().props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const flattenedSettings = computed(() => {
  const settings = pageProps.settings;
  if (!settings || typeof settings !== "object") return [];

  const flat = [];

  Object.entries(group.items).forEach(([itemKey, item]) => {
    if (item && typeof item === "object") {
      flat.push({
        id: `${groupKey}.${itemKey}`,
        groupKey,
        itemKey,
        name: item.name || item.label || itemKey,
        category: group.label || groupKey,
        path: item.path || `${groupKey}.${itemKey}`,
        type: item.type,
        label: item.label,
        description: item.description,
        groupLabel: group.label,
        groupDescription: group.description,
        items: item.items || [],
        value: item.value,
        options: item.options,
        ...item,
      });
    }
  });

  return flat;
});
const search = ref("");
const filteredSettings = computed(() => {
  if (!search.value) {
    return pageProps.settings;
  }
  const term = search.value.toLowerCase();
  const filteredGroups = {};
  Object.entries(pageProps.settings).forEach(([groupKey, group]) => {
    if (!group || !group.items) return;
    const groupMatches =
      (t(group.label) && t(group.label).toLowerCase().includes(term)) ||
      (group.description && group.description.toLowerCase().includes(term)) ||
      groupKey.toLowerCase().includes(term);

    const filteredItems = {};

    Object.entries(group.items).forEach(([itemKey, item]) => {
      if (!item) return;

      const itemMatches =
        (t(item.label) && t(item.label).toLowerCase().includes(term)) ||
        (item.description && item.description.toLowerCase().includes(term)) ||
        (item.name && item.name.toLowerCase().includes(term)) ||
        (item.path && item.path.toLowerCase().includes(term)) ||
        itemKey.toLowerCase().includes(term);

      if (itemMatches) {
        filteredItems[itemKey] = item;
      }
    });

    if (groupMatches || Object.keys(filteredItems).length > 0) {
      filteredGroups[groupKey] = {
        ...group,
        items: groupMatches ? group.items : filteredItems,
      };
    }
  });

  return filteredGroups;
});
</script>

<template>
  <Head>
    <title>{{ $t("settings.label") }}</title>
  </Head>
  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
    }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <h3 class="settings__header__title__text">
          {{ $t("settings.label") }}
        </h3>
      </div>
      <div class="settings__header__search">
        <input
          v-model="search"
          type="search"
          class="setting-search"
          :placeholder="t('settings.search_placeholder')"
        />
      </div>
    </div>
    <div class="settings__list">
      <div v-for="s in filteredSettings" class="settings__list__section">
        <div class="settings__list__section__header">
          <div class="settings__list__section__header__title">
            <h6>{{ $t(s.label) }}</h6>
          </div>
          <div class="settings__list__section__header__desc">
            <p>{{ $t(s.description) }}</p>
          </div>
        </div>
        <div class="settings__list__section__links">
          <Link v-for="i in s.items" :href="i.path">
            <i :class="i.icon"></i>
            <span class="label">{{ $t(i.label) }}</span>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
