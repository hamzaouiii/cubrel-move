<script setup>
import axios from "axios";
import { ref, computed, onMounted, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import { formatDate, formatDateTime } from "@/utils/datetime";
import ImpersonationBadge from "@/Pages/Components/Settings/AuditTrail/ImpersonationBadge.vue";

const props = defineProps({
  moduleSlug: { type: String, required: true },
  recordId: { type: String, required: true },
  fields: { type: Array, default: () => [] },
});

const emit = defineEmits(["close"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;
const allModules = computed(() => usePage().props.modules ?? []);

// phases: loading | ready | error
const phase = ref("loading");
const entries = ref([]);
const page = ref(1);
const lastPage = ref(1);
const errorMessage = ref("");
const loadingMore = ref(false);

const canLoadMore = computed(() => page.value < lastPage.value);

const fieldDef = (name) => props.fields?.find((f) => f.name === name);

const fieldLabel = (name) => {
  const field = fieldDef(name);
  return field?.label ? t(field.label) : name;
};

const dropdownLabel = (name, rawValue) => {
  const values = fieldDef(name)?.dropdown_list?.values;
  if (!values) return null;
  const match = values.find((v) => String(v.value) === String(rawValue));
  return match ? t(match.label) : null;
};

const formatValue = (name, value) => {
  if (value === null || value === undefined || value === "") {
    return t("globals.audit_trail.empty_value");
  }
  const type = fieldDef(name)?.type;
  if (type === "date") return formatDate(value, appSettings);
  if (type === "datetime") return formatDateTime(value, appSettings);
  if (["bool", "boolean", "checkbox"].includes(type)) {
    return value ? t("globals.audit_trail.yes") : t("globals.audit_trail.no");
  }
  if (["select", "dropdown", "status"].includes(type)) {
    return dropdownLabel(name, value) ?? String(value);
  }
  return String(value);
};

const when = (value) => formatDateTime(value, appSettings);

// 'record' type fields (e.g. owner_id) carry a resolved *_label alongside
// the raw id — prefer that, since a bare id means nothing to a viewer.
const diffValue = (name, diff, which) => {
  const label = diff[`${which}_label`];
  if (label !== undefined) {
    return label ?? t("globals.audit_trail.empty_value");
  }
  return formatValue(name, diff[which]);
};

const isBulkChange = (changes) => !!changes && changes.count !== undefined;

const bulkSummary = (entry) => {
  const c = entry.changes;
  return t("globals.audit_trail.bulk_summary_detail", {
    count: c.count,
    field: fieldLabel(c.field),
    value: formatValue(c.field, c.value),
  });
};

const isLinkChange = (entry) =>
  entry.action === "linked" || entry.action === "unlinked";

const relatedModuleLabel = (slug) =>
  allModules.value.find((m) => m.slug === slug)?.label ?? slug;

const linkSummary = (entry) => {
  const c = entry.changes;
  return t("globals.audit_trail.link_summary", {
    related: c.related_label ?? c.related_id,
    module: relatedModuleLabel(c.related_module),
  });
};

const load = async (nextPage = 1) => {
  if (nextPage === 1) {
    phase.value = "loading";
  } else {
    loadingMore.value = true;
  }

  try {
    const { data } = await axios.get(
      `/modules/${props.moduleSlug}/${props.recordId}/history`,
      { params: { page: nextPage } },
    );
    entries.value = nextPage === 1 ? data.data : [...entries.value, ...data.data];
    page.value = data.meta.current_page;
    lastPage.value = data.meta.last_page;
    phase.value = "ready";
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message ||
      err.message ||
      "An unexpected error occurred while loading the history.";
    phase.value = "error";
  } finally {
    loadingMore.value = false;
  }
};

const loadMore = () => load(page.value + 1);

const close = () => emit("close");

onMounted(() => load(1));
</script>

<template>
  <div class="pdf-modal history-modal">
    <div class="pdf-modal__backdrop" @click="close"></div>

    <div class="pdf-modal__container history-modal__container">
      <div class="deployment-card">
        <div class="deployment-card__header">
          <div class="deployment-card__title-group">
            <h3 class="deployment-card__title">
              {{ $t("globals.audit_trail.modal_title") }}
            </h3>
            <p class="deployment-card__subtitle">
              {{ $t("globals.audit_trail.modal_subtitle") }}
            </p>
          </div>
        </div>

        <div class="pdf-modal__body history-modal__body">
          <div v-if="phase === 'loading'" class="history-modal__loading">
            {{ $t("modules.loading") }}
          </div>

          <div v-else-if="phase === 'error'" class="history-modal__error">
            {{ errorMessage }}
          </div>

          <div v-else-if="entries.length === 0" class="history-modal__empty">
            {{ $t("globals.audit_trail.no_logs") }}
          </div>

          <ul v-else class="history-modal__list">
            <li
              v-for="entry in entries"
              :key="entry.id"
              class="history-modal__entry"
            >
              <div class="history-modal__entry__meta">
                <span
                  class="audit-trail__action-badge"
                  :class="`audit-trail__action-badge--${entry.action}`"
                >
                  {{ $t(`globals.audit_trail.action_labels.${entry.action}`) }}
                </span>
                <span class="history-modal__entry__actor">
                  {{
                    entry.user?.name ?? $t("globals.audit_trail.unknown_actor")
                  }}
                </span>
                <ImpersonationBadge :impersonator="entry.impersonator" />
                <span class="history-modal__entry__when">{{
                  when(entry.created_at)
                }}</span>
              </div>

              <p
                v-if="entry.action === 'deleted' && entry.changes?.record_label"
                class="history-modal__entry__bulk"
              >
                {{
                  $t("globals.audit_trail.deleted_record_label", {
                    name: entry.changes.record_label,
                  })
                }}
              </p>

              <p
                v-else-if="isLinkChange(entry)"
                class="history-modal__entry__bulk"
              >
                {{ linkSummary(entry) }}
              </p>

              <p
                v-else-if="isBulkChange(entry.changes)"
                class="history-modal__entry__bulk"
              >
                {{ bulkSummary(entry) }}
              </p>

              <table
                v-else-if="entry.changes && Object.keys(entry.changes).length"
                class="history-modal__entry__changes"
              >
                <tbody>
                  <tr v-for="(diff, field) in entry.changes" :key="field">
                    <th>{{ fieldLabel(field) }}</th>
                    <td class="history-modal__entry__changes__old">
                      {{ diffValue(field, diff, "old") }}
                    </td>
                    <td class="history-modal__entry__changes__arrow">
                      <i class="fa-solid fa-arrow-right"></i>
                    </td>
                    <td class="history-modal__entry__changes__new">
                      {{ diffValue(field, diff, "new") }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </li>
          </ul>
        </div>

        <div class="deployment-card__footer" v-if="phase === 'ready'">
          <div class="deployment-card__footer__content">
            <button
              v-if="canLoadMore"
              class="deployment-card__button deployment-card__button--secondary"
              :disabled="loadingMore"
              @click="loadMore"
            >
              {{ $t("globals.audit_trail.load_more") }}
            </button>
          </div>
        </div>
      </div>

      <button class="pdf-modal__close" @click="close">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M18 6L6 18M6 6L18 18" stroke-width="2" stroke-linecap="round" />
        </svg>
      </button>
    </div>
  </div>
</template>
