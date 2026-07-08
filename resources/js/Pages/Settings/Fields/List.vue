<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed, getCurrentInstance } from "vue";
import ModuleSettingsHeader from "@/Pages/Components/Settings/ModuleSettingsHeader.vue";
import { useConfirm } from "@/Composables/useConfirm";
import { useAlerts } from "@/Composables/useAlerts";

const { confirm } = useConfirm();
const { info, success, error, clearAllAlerts } = useAlerts();
const { proxy } = getCurrentInstance();
const t = proxy.$t;

defineOptions({
  layout: [AppLayout, SettingsLayout],
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

const deleteField = async (f) => {
  let msg;
  let highlt;
  if (f.records_using === 1) {
    msg = t("fields.confirm.delete_msg_singular", { count: 1 });
    highlt = 1;
  } else if (f.records_using > 1) {
    msg = t("fields.confirm.delete_msg", { count: f.records_using });
    highlt = f.records_using;
  } else {
    msg = t("fields.confirm.delete_msg_no_count");
    highlt = null;
  }

  const ok = await confirm({
    title: t("fields.confirm.delete_title"),
    message: msg,
    confirmText: t("fields.confirm.delete_confirm"),
    cancelText: t("fields.confirm.delete_cancel"),
    danger: true,
    highlight: highlt,
  });
  if (!ok) return;

  router.delete(`${page.url.replace(/\/+$/, "")}/${f.name}`, {
    preserveScroll: true,
    onStart: () => {
      clearAllAlerts();
      info(t("fields.deleting"));
    },
    onSuccess: () => {
      clearAllAlerts();
      success(t("fields.field_delete_success"));
    },
    onError: (e) => {
      clearAllAlerts();
      error(e.field);
    },
  });
};
</script>

<template>
  <Head>
    <title>
      {{ module.label }} - {{ $t("fields.label") }} -
      {{ $t("settings.label") }} - Cubrel
    </title>
  </Head>
  <div class="settings" :style="{ '--module-color': color() }">
    <div class="settings__module">
      <ModuleSettingsHeader
        :setting-module="module"
        active-key="fields"
      ></ModuleSettingsHeader>
    </div>

    <div class="fields">
      <div class="fields__header">
        <span class="fields__header__name">
          {{ module?.label || module?.name }}</span
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
            <th style="width: 70px"></th>
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
            <td style="width: 70px" class="fields__table__row__actions" @click.stop>
              <button
                class="fields__table__row__actions__delete"
                :disabled="!f.is_custom"
                @click="deleteField(f)"
              >
                <i
                  class="fields__table__row__actions__delete__icon fa-solid fa-trash"
                ></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
