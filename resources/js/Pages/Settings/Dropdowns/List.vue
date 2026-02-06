<script setup>
import { computed, ref } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  item: Object,
  list: Array,
});
const page = usePage();
const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});

const sortKey = ref(null);
const sortDirection = ref("asc");

function sortBy(key) {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = key;
    sortDirection.value = "asc";
  }
}
const sortedFields = computed(() => {
  if (!sortKey.value) return props.list;

  return [...props.list].sort((a, b) => {
    const valA = a[sortKey.value] ?? "";
    const valB = b[sortKey.value] ?? "";

    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});
const editUrl = (f) => {
  return `${page.url.replace(/\/+$/, "")}/${f}`;
};
</script>
<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="item"
        ></ModuleSettingBreadcrumbs>
      </div>
      <div class="settings__header__action">
        <Link class="settings__header__action__create" :href="createUrl">
          {{ $t("settings.dropdown.create") }}</Link
        >
      </div>
    </div>

    <div class="fields">
      <table class="fields__table">
        <thead>
          <tr>
            <th @click="sortBy('key')">
              {{ $t("key") }}

              <i
                v-if="sortKey === 'key'"
                class="fa-solid sort-icon is-active"
                :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
              ></i>

              <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
            </th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr class="fields__table__row" v-for="f in sortedFields" :key="f.key">
            <td>
              {{ f.key }}
            </td>
            <td class="fields__table__row__actions">
              <span
                class="fields__table__row__actions__delete btn fields__table__row__actions__delete--disabled"
              >
                <i
                  class="fields__table__row__actions__delete__icon fa-solid fa-trash-can"
                ></i>
              </span>
              <Link
                class="fields__table__row__actions__edit btn"
                :href="editUrl(f.key)"
              >
                <i
                  class="fields__table__row__actions__edit__icon fa-solid fa-pen-to-square"
                ></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
