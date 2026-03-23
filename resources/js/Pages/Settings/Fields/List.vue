<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  fields: Array,
});

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
const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});
const editUrl = (f) => {
  return `${page.url.replace(/\/+$/, "")}/${f}`;
};

const color = () =>
  appSettings.use_individual_module_colors
    ? props.module.color
    : appSettings.primary_color;
</script>

<template>
  <Head>
    <title>
      {{ module.label }} - {{ $t("fields.label") }} - {{ $t("settings.label") }}
    </title>
  </Head>
  <div class="settings" :style="{ '--module-color': color() }">
    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="fields"
      ></ModuleSettingTabs>
    </div>

    <div class="fields">
      <div class="fields__header">
        <span class="fields__header__name">
          {{ module?.display_label || module?.name }}</span
        >

        <Link class="fields__header__create" :href="createUrl">
          {{ $t("fields.create_new_field") }}</Link
        >
      </div>
      <table class="fields__table">
        <thead>
          <tr>
            <th @click="sortBy('key')">
              {{ $t("fields.name") }}

              <i
                v-if="sortKey === 'key'"
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
          </tr>
        </thead>

        <tbody>
          <tr
            class="fields__table__row"
            v-for="f in sortedFields"
            :key="f.key"
            @click="router.visit(editUrl(f.name))"
          >
            <td>
              {{ f.name }}
            </td>
            <td>{{ $t(f.label) }}</td>
            <td>{{ $t("fields.types." + f.type) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
