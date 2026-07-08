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
defineOptions({
  layout: [AppLayout, SettingsLayout],
});

const props = defineProps({
  module: Object,
  relationships: Object,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;

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

const sortedRelationships = computed(() => {
  if (!sortKey.value) return props.relationships;

  return [...props.relationships].sort((a, b) => {
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

const moduleColor = () =>
  appSettings.use_individual_module_colors
    ? props.module.color
    : appSettings.primary_color;

const currentModule = page.props.module.id;
const deleteRelationship = async (rel) => {
  let msg;
  let highlt;
  if (rel.links_used === 1) {
    msg = t("relationships.confirm.delete_msg_singular", { count: 1 });
    highlt = 1;
  } else if (rel.links_used > 1) {
    msg = t("relationships.confirm.delete_msg", {
      count: rel.links_used,
    });
    highlt = rel.links_used;
  } else {
    msg = t("relationships.confirm.delete_msg_no_count");
    highlt = null;
  }
  const ok = await confirm({
    title: t("relationships.confirm.delete_title"),
    message: msg,
    confirmText: t("relationships.confirm.delete_confirm"),
    cancelText: t("relationships.confirm.delete_cancel"),
    danger: true,
    highlight: highlt,
  });
  if (!ok) return;
  router.delete(`/settings/modules/${currentModule}/relationships/${rel.id}`, {
    preserveScroll: true,
    onStart: () => {
      clearAllAlerts();
      info(t("relationships.deleting"));
    },
    onSuccess: () => {
      clearAllAlerts();
      success(t("relationships.deleting_success"));
    },
    onError: (e) => {
      clearAllAlerts();
      error(e.rel);
    },
  });
};
</script>

<template>
  <Head>
    <title>
      {{ module.label }} - {{ $t("relationships.label") }} -
      {{ $t("settings.label") }} - Cubrel
    </title>
  </Head>
  <div
    class="settings"
    :style="[
      { '--module-color': moduleColor() },
      { '--danger-color': appSettings.danger_color },
    ]"
  >
    <div class="settings__module">
      <ModuleSettingsHeader
        :setting-module="module"
        active-key="relationships"
      ></ModuleSettingsHeader>
    </div>
    <div class="fields">
      <div class="fields__header">
        <span class="fields__header__name">
          {{ module?.label || module?.name }}</span
        >

        <Link class="fields__header__create" :href="createUrl">
          {{ $t("relationships.create_new") }}</Link
        >
      </div>
      <table class="fields__table">
        <thead>
          <tr>
            <th @click="sortBy('name')">
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
            v-for="r in sortedRelationships"
            :key="r.key"
          >
            <td>
              {{ r.name }}
            </td>
            <td>{{ $t(r.label) }}</td>
            <td>{{ $t("relationships.types." + r.type) }}</td>
            <td style="width: 70px" class="fields__table__row__actions">
              <button
                class="fields__table__row__actions__delete"
                :disabled="r.is_system"
                @click="deleteRelationship(r)"
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
