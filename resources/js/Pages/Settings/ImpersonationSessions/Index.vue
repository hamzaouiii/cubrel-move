<script setup>
import { computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import { formatDateTime } from "@/utils/datetime";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;

const props = defineProps({
  sessions: Array,
  meta: Object,
  filters: Object,
  users: Array,
});

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.impersonation_sessions") },
];

const applyFilter = (patch) => {
  router.get(
    window.location.pathname,
    {
      impersonator_id: props.filters?.impersonator_id || undefined,
      target_user_id: props.filters?.target_user_id || undefined,
      date_from: props.filters?.date_from || undefined,
      date_to: props.filters?.date_to || undefined,
      ...patch,
      page: 1,
    },
    { preserveState: true, preserveScroll: true, replace: true },
  );
};

const hidePagination = computed(
  () => (props.meta?.total ?? 0) <= (props.meta?.perPage ?? 15),
);

const impersonatorOptions = computed(() => ({
  values: [
    {
      value: null,
      label: t("globals.impersonation_sessions.filters.all_impersonators"),
    },
    ...(props.users ?? []).map((u) => ({ value: u.id, label: u.name })),
  ],
}));

const targetOptions = computed(() => ({
  values: [
    {
      value: null,
      label: t("globals.impersonation_sessions.filters.all_targets"),
    },
    ...(props.users ?? []).map((u) => ({ value: u.id, label: u.name })),
  ],
}));

const toDateParam = (date) => {
  if (!date) return undefined;
  const d = date instanceof Date ? date : new Date(date);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
};

const formatDuration = (seconds) => {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = Math.floor(seconds % 60);
  return [h, m, s].map((v) => String(v).padStart(2, "0")).join(":");
};

const when = (value) => formatDateTime(value, appSettings);

const metaSentence = computed(() => {
  return `${props.sessions?.length ?? 0} ${t("modules.of")} ${props.meta?.total ?? 0}`;
});
</script>

<template>
  <Head>
    <title>{{ $t("globals.impersonation_sessions.page_title") }}</title>
  </Head>

  <div
    class="settings impersonation-sessions"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--module-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
      '--danger-color': appSettings.danger_color,
    }"
  >
    <div class="settings__module__header">
      <SettingsBreadcrumb :crumbs="crumbs" />
    </div>

    <div class="impersonation-sessions__header">
      <div class="impersonation-sessions__header__details">
        <span class="impersonation-sessions__header__details__meta">
          {{ metaSentence }}
        </span>
      </div>
    </div>

    <div class="impersonation-sessions__filters">
      <div class="impersonation-sessions__filters__group">
        <label>{{
          $t("globals.impersonation_sessions.columns.impersonator")
        }}</label>
        <Select
          class="impersonation-sessions__filters__field"
          :model-value="filters?.impersonator_id || null"
          :dropdown_list="impersonatorOptions"
          nullable
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ impersonator_id: val || undefined })
          "
        />
      </div>

      <div class="impersonation-sessions__filters__group">
        <label>{{
          $t("globals.impersonation_sessions.columns.target_user")
        }}</label>
        <Select
          class="impersonation-sessions__filters__field"
          :model-value="filters?.target_user_id || null"
          :dropdown_list="targetOptions"
          nullable
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ target_user_id: val || undefined })
          "
        />
      </div>

      <div class="impersonation-sessions__filters__group">
        <label>{{ $t("globals.audit_trail.filters.date_from") }}</label>
        <DateTime
          class="impersonation-sessions__filters__field"
          :model-value="filters?.date_from || null"
          type="date"
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ date_from: toDateParam(val) })
          "
        />
      </div>
      <div class="impersonation-sessions__filters__group">
        <label>{{ $t("globals.audit_trail.filters.date_to") }}</label>
        <DateTime
          class="impersonation-sessions__filters__field"
          :model-value="filters?.date_to || null"
          type="date"
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ date_to: toDateParam(val) })
          "
        />
      </div>
    </div>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
            <th>
              {{ $t("globals.impersonation_sessions.columns.impersonator") }}
            </th>
            <th>
              {{ $t("globals.impersonation_sessions.columns.target_user") }}
            </th>
            <th>
              {{ $t("globals.impersonation_sessions.columns.ip_address") }}
            </th>
            <th>
              {{ $t("globals.impersonation_sessions.columns.started_at") }}
            </th>
            <th>{{ $t("globals.impersonation_sessions.columns.ended_at") }}</th>
            <th>{{ $t("globals.impersonation_sessions.columns.duration") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="sessions.length === 0">
            <td colspan="6" class="impersonation-sessions__empty">
              {{ $t("globals.impersonation_sessions.no_sessions") }}
            </td>
          </tr>
          <tr v-for="session in sessions" :key="session.id">
            <td>{{ session.impersonator?.name }}</td>
            <td>{{ session.target_user?.name }}</td>
            <td class="impersonation-sessions__cell-ip">
              {{ session.ip_address }}
            </td>
            <td>{{ when(session.started_at) }}</td>
            <td>
              <span
                v-if="session.ongoing"
                class="impersonation-sessions__badge impersonation-sessions__badge--ongoing"
              >
                {{ $t("globals.impersonation_sessions.duration_ongoing") }}
              </span>
              <span v-else>{{ when(session.ended_at) }}</span>
            </td>
            <td>
              <span
                v-if="!session.ongoing"
                class="impersonation-sessions__duration"
              >
                {{ formatDuration(session.duration_seconds) }}
              </span>
              <span v-else class="impersonation-sessions__duration">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="list-layout__pagination" v-if="!hidePagination">
      <Pagination v-if="meta && meta.total !== 0" :meta="meta" />
    </div>
  </div>
</template>
