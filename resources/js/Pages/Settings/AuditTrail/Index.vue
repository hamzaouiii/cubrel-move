<script setup>
import { computed, ref, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";
import SettingsBreadcrumb from "@/Pages/Components/Settings/SettingsBreadcrumb.vue";
import ImpersonationBadge from "@/Pages/Components/Settings/AuditTrail/ImpersonationBadge.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import HistoryModal from "@/Pages/Components/Modules/HistoryModal.vue";
import BulkAffectedRecordsModal from "@/Pages/Components/Modules/BulkAffectedRecordsModal.vue";
import { formatDateTime } from "@/utils/datetime";

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;

const props = defineProps({
  logs: Array,
  meta: Object,
  filters: Object,
  audit_modules: Array,
  fields_by_module: Object,
  users: Array,
});

const crumbs = [
  { label: t("settings.label"), href: "/settings" },
  { label: t("settings.items.audit_trail") },
];

const ACTIONS = ["created", "updated", "deleted", "linked", "unlinked"];

const applyFilter = (patch) => {
  router.get(
    window.location.pathname,
    {
      module: props.filters?.module || undefined,
      user_id: props.filters?.user_id || undefined,
      action: props.filters?.action || undefined,
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

const moduleOptions = computed(() => ({
  values: [
    { value: null, label: t("globals.audit_trail.filters.all_modules") },
    ...(props.audit_modules ?? []).map((m) => ({
      value: m.slug,
      label: m.label,
    })),
  ],
}));

const userOptions = computed(() => ({
  values: [
    { value: null, label: t("globals.audit_trail.filters.all_users") },
    ...(props.users ?? []).map((u) => ({ value: u.id, label: u.name })),
  ],
}));

const actionOptions = computed(() => ({
  values: [
    { value: null, label: t("globals.audit_trail.filters.all_actions") },
    ...ACTIONS.map((a) => ({
      value: a,
      label: `globals.audit_trail.action_labels.${a}`,
    })),
  ],
}));

// DateTime emits a Date object; the backend filters expect a plain
// YYYY-MM-DD string (matching the native <input type="date"> this replaced).
const toDateParam = (date) => {
  if (!date) return undefined;
  const d = date instanceof Date ? date : new Date(date);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
};

const moduleLabel = (slug) => {
  const module = props.audit_modules?.find((m) => m.slug === slug);
  return module?.label ?? slug ?? t("globals.audit_trail.no_module");
};

const moduleColor = (slug) => {
  if (appSettings.use_individual_module_colors == "0") {
    return appSettings.primary_color;
  }
  const module = props.audit_modules?.find((m) => m.slug === slug);
  return module?.color ?? appSettings.primary_color;
};

const fieldLabel = (moduleSlug, fieldName) => {
  const field = props.fields_by_module?.[moduleSlug]?.find(
    (f) => f.name === fieldName,
  );
  return field?.label ? t(field.label) : fieldName;
};

const changesSummary = (log) => {
  if (!log.changes) return "";

  if (log.action === "linked" || log.action === "unlinked") {
    return t("globals.audit_trail.link_summary", {
      related: log.changes.related_label ?? log.changes.related_id,
      module: moduleLabel(log.changes.related_module),
    });
  }

  if (log.action === "deleted") {
    if (log.changes.count !== undefined) {
      return t("globals.audit_trail.bulk_delete_summary", {
        count: log.changes.count,
      });
    }
    if (log.changes.record_label) {
      return log.changes.record_label;
    }
  }

  if (log.changes.count !== undefined) {
    return t("globals.audit_trail.bulk_summary", {
      count: log.changes.count,
      field: fieldLabel(log.module_slug, log.changes.field),
    });
  }
  return Object.keys(log.changes)
    .map((field) => fieldLabel(log.module_slug, field))
    .join(", ");
};

const when = (value) => formatDateTime(value, appSettings);

const selectedRecord = ref(null);
const selectedBulkLog = ref(null);

const isBulkRow = (log) => !log.record_id && log.changes?.count !== undefined;

const openHistory = (log) => {
  if (log.record_id) {
    selectedRecord.value = {
      moduleSlug: log.module_slug,
      recordId: log.record_id,
      fields: props.fields_by_module?.[log.module_slug] ?? [],
    };
    return;
  }

  if (isBulkRow(log)) {
    selectedBulkLog.value = {
      auditLogId: log.id,
      fields: props.fields_by_module?.[log.module_slug] ?? [],
    };
  }
};

const metaSentence = computed(() => {
  return `${props.logs?.length ?? 0} ${t("modules.of")} ${props.meta?.total ?? 0}`;
});
</script>

<template>
  <Head>
    <title>{{ $t("globals.audit_trail.page_title") }}</title>
  </Head>

  <div
    class="settings audit-trail"
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

    <div class="audit-trail__header">
      <div class="audit-trail__header__details">
        <span class="audit-trail__header__details__meta">
          {{ metaSentence }}
        </span>
      </div>
    </div>

    <div class="audit-trail__filters">
      <div class="audit-trail__filters__group">
        <label>{{ $t("globals.audit_trail.filters.module_label") }}</label>
        <Select
          class="audit-trail__filters__field"
          :model-value="filters?.module || null"
          :dropdown_list="moduleOptions"
          nullable
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ module: val || undefined })
          "
        />
      </div>

      <div class="audit-trail__filters__group">
        <label>{{ $t("globals.audit_trail.filters.user_label") }}</label>
        <Select
          class="audit-trail__filters__field"
          :model-value="filters?.user_id || null"
          :dropdown_list="userOptions"
          nullable
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ user_id: val || undefined })
          "
        />
      </div>

      <div class="audit-trail__filters__group">
        <label>{{ $t("globals.audit_trail.filters.action_label") }}</label>
        <Select
          class="audit-trail__filters__field"
          :model-value="filters?.action || null"
          :dropdown_list="actionOptions"
          nullable
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ action: val || undefined })
          "
        />
      </div>

      <div class="audit-trail__filters__group">
        <label>{{ $t("globals.audit_trail.filters.date_from") }}</label>
        <DateTime
          class="audit-trail__filters__field"
          :model-value="filters?.date_from || null"
          type="date"
          mode="edit"
          @update:model-value="
            (val) => applyFilter({ date_from: toDateParam(val) })
          "
        />
      </div>
      <div class="audit-trail__filters__group">
        <label>{{ $t("globals.audit_trail.filters.date_to") }}</label>
        <DateTime
          class="audit-trail__filters__field"
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
      <table class="list-layout__table audit-trail__table">
        <thead>
          <tr>
            <th>{{ $t("globals.audit_trail.columns.when") }}</th>
            <th>{{ $t("globals.audit_trail.columns.module_record") }}</th>
            <th>{{ $t("globals.audit_trail.columns.action") }}</th>
            <th>{{ $t("globals.audit_trail.columns.actor") }}</th>
            <th>{{ $t("globals.audit_trail.columns.changes") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="logs.length === 0">
            <td colspan="5" class="audit-trail__empty">
              {{ $t("globals.audit_trail.no_logs") }}
            </td>
          </tr>
          <tr
            v-for="log in logs"
            :key="log.id"
            :class="{ 'audit-trail__row--clickable': log.record_id || isBulkRow(log) }"
            @click="openHistory(log)"
          >
            <td class="audit-trail__cell-when">{{ when(log.created_at) }}</td>
            <td>
              <span
                class="audit-trail__module-badge"
                :style="{ '--module-color': moduleColor(log.module_slug) }"
              >
                {{ moduleLabel(log.module_slug) }}
              </span>
            </td>
            <td>
              <span
                class="audit-trail__action-badge"
                :class="`audit-trail__action-badge--${log.action}`"
              >
                {{ $t(`globals.audit_trail.action_labels.${log.action}`) }}
              </span>
            </td>
            <td>
              <span class="audit-trail__actor">
                {{ log.user?.name ?? $t("globals.audit_trail.unknown_actor") }}
              </span>
              <ImpersonationBadge :impersonator="log.impersonator" />
            </td>
            <td class="audit-trail__cell-changes">{{ changesSummary(log) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="list-layout__pagination" v-if="!hidePagination">
      <Pagination v-if="meta && meta.total !== 0" :meta="meta" />
    </div>

    <HistoryModal
      v-if="selectedRecord"
      :module-slug="selectedRecord.moduleSlug"
      :record-id="selectedRecord.recordId"
      :fields="selectedRecord.fields"
      @close="selectedRecord = null"
    />

    <BulkAffectedRecordsModal
      v-if="selectedBulkLog"
      :audit-log-id="selectedBulkLog.auditLogId"
      :fields="selectedBulkLog.fields"
      @close="selectedBulkLog = null"
    />
  </div>
</template>
