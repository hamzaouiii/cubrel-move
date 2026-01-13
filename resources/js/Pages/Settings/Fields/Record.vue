<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  fields: Array,
  item: Object,
});

console.log(props.fields);
const page = usePage();
const appSettings = page.props.appSettings;

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
const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});
const editUrl = (f) => {
  return `${page.url.replace(/\/+$/, "")}/${f}`;
};
const sortedFields = computed(() => {
  if (!sortKey.value) return props.fields;

  return [...props.fields].sort((a, b) => {
    const valA = a[sortKey.value] ?? "";
    const valB = b[sortKey.value] ?? "";

    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});
</script>

<template>
  <Head>
    <title>
      {{ module.label }} - {{ $t("fields.label") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>
    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="fields"
      ></ModuleSettingTabs>
    </div>

    <div class="fields">
      <div class="fields__header">
        <Link class="fields__header_create btn" :href="createUrl">
          {{ $t("fields.create_new_field") }}</Link
        >
      </div>
      <table class="fields__table">
        <thead>
          <tr>
            <th @click="sortBy('name')">
              {{ $t("fields.name") }}

              <i
                v-if="sortKey === 'name'"
                class="fa-solid sort-icon is-active"
                :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
              ></i>

              <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
            </th>

            <th @click="sortBy('label')">
              {{ $t("fields.field_label") }}

              <i
                v-if="sortKey === 'label'"
                class="fa-solid sort-icon is-active"
                :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
              ></i>

              <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
            </th>

            <th @click="sortBy('type')">
              {{ $t("fields.type") }}

              <i
                v-if="sortKey === 'type'"
                class="fa-solid sort-icon is-active"
                :class="sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down'"
              ></i>

              <i v-else class="fa-solid fa-sort sort-icon hover-icon"></i>
            </th>
            <th style="width: 70px"></th>
          </tr>
        </thead>

        <tbody>
          <tr class="fields__table__row" v-for="f in sortedFields" :key="f.key">
            <td>
              {{ f.name }}
            </td>
            <td>{{ $t(f.label) }}</td>
            <td>{{ $t("fields.types." + f.type) }}</td>
            <td style="width: 70px">
              <Link class="fields__table__row__edit btn" :href="editUrl(f.key)">
                <i
                  class="fields__table__row__edit__icon fa-regular fa-pen-to-square"
                ></i>
              </Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
