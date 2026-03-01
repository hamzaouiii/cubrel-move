<script setup>
import { computed, ref } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import DropdownBreadcrumbs from "@/Pages/Components/Settings/Dropdowns/DropdownBreadcrumbs.vue";

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

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/
const search = ref("");

const filteredFields = computed(() => {
  if (!search.value) return props.list;

  return props.list.filter((f) =>
    String(f.key).toLowerCase().includes(search.value.toLowerCase()),
  );
});

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/
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
  if (!sortKey.value) return filteredFields.value;

  return [...filteredFields.value].sort((a, b) => {
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
        <DropdownBreadcrumbs :setting-module="item" />
      </div>

      <div class="settings__header__action">
        <Link class="settings__header__action__create" :href="createUrl">
          {{ $t("settings.dropdown.create") }}
        </Link>
      </div>
    </div>

    <!-- Search -->
    <div class="dropdowns__search">
      <input
        v-model="search"
        type="text"
        class="dropdowns__search__input"
        :placeholder="$t('settings.search')"
      />
    </div>

    <div class="dropdowns">
      <table class="dropdowns__table">
        <tbody>
          <tr
            class="dropdowns__table__row"
            v-for="f in sortedFields"
            :key="f.key"
          >
            <td @click="sortBy('key')" style="cursor: pointer">
              {{ f.key }}
            </td>

            <td class="dropdowns__table__row__actions">
              <span
                class="dropdowns__table__row__actions__delete btn dropdowns__table__row__actions__delete--disabled"
              >
                <i
                  class="dropdowns__table__row__actions__delete__icon fa-solid fa-trash-can"
                ></i>
              </span>

              <Link
                class="dropdowns__table__row__actions__edit btn"
                :href="editUrl(f.key)"
              >
                <i
                  class="dropdowns__table__row__actions__edit__icon fa-solid fa-pen-to-square"
                ></i>
              </Link>
            </td>
          </tr>

          <tr v-if="!sortedFields.length">
            <td colspan="2" style="text-align: center; padding: 1rem">
              {{ $t("settings.no_results") }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
