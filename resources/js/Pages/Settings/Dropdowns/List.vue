<script setup>
import { computed, ref, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.dropdowns") },
];

const appSettings = usePage().props.appSettings;

defineOptions({
  layout: [AppLayout, SettingsLayout],
});

const props = defineProps({
  item: Object,
  list: Array,
});

const page = usePage();

const createUrl = computed(() => {
  return `${page.url.replace(/\/+$/, "")}/create`;
});

const search = ref("");

const filteredFields = computed(() => {
  if (!search.value) return props.list;

  return props.list.filter((f) =>
    String(f.key).toLowerCase().includes(search.value.toLowerCase()),
  );
});

const editUrl = (f) => {
  return `${page.url.replace(/\/+$/, "")}/${f}`;
};
</script>

<template>
  <Head>
    <title>
      {{ $t("settings.items.dropdowns") }} - {{ $t("settings.label") }} - Cubrel
    </title>
  </Head>

  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>
    <div class="dropdowns__toolbar">
      <div class="dropdowns__search">
        <input
          v-model="search"
          type="text"
          class="dropdowns__search__input"
          :placeholder="$t('settings.dropdown.search')"
        />
      </div>

      <div class="dropdowns__actions">
        <Link class="btn-create" :href="createUrl">
          <i class="fa-solid fa-plus"></i>
          {{ $t("settings.dropdown.create") }}
        </Link>
      </div>
    </div>
    <div class="dropdowns">
      <table class="dropdowns__table">
        <tbody>
          <tr
            class="dropdowns__table__row"
            v-for="f in filteredFields"
            :key="f.key"
          >
            <td style="cursor: pointer">
              {{ f.key }}
            </td>

            <td class="dropdowns__table__row__actions">
              <!-- <span
                class="dropdowns__table__row__actions__delete btn dropdowns__table__row__actions__delete--disabled"
              >
                <i
                  class="dropdowns__table__row__actions__delete__icon fa-solid fa-trash-can"
                ></i>
              </span> -->

              <Link
                class="dropdowns__table__row__actions__edit"
                :href="editUrl(f.id)"
              >
                <i
                  class="dropdowns__table__row__actions__edit__icon fa-solid fa-pen-to-square"
                ></i>
              </Link>
            </td>
          </tr>

          <tr v-if="!filteredFields.length">
            <td colspan="2" style="text-align: center; padding: 1rem">
              {{ $t("settings.no_results") }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
