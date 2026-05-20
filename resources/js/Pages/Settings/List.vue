<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, getCurrentInstance } from "vue";

defineOptions({
  layout: AppLayout,
});

const pageProps = defineProps({
  settings: Object,
});
const appSettings = usePage().props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

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

const clearSearch = () => {
  search.value = "";
};
</script>

<template>
  <Head>
    <title>{{ $t("settings.label") }} - Cubrel</title>
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
          class="settings__header__search__field"
          :placeholder="t('settings.search_placeholder')"
        />
        <i
          v-if="search"
          class="fa-solid fa-xmark settings__header__search__clear"
          @click="clearSearch"
        ></i>
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
    <div
      v-if="Object.keys(filteredSettings).length === 0"
      class="settings__empty"
    >
      <i class="fa-solid fa-exclamation"></i>
      <p>{{ $t("settings.no_results") }}</p>
    </div>
  </div>
</template>
