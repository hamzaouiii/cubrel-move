<script setup>
import axios from "axios";
import {
  ref,
  reactive,
  computed,
  onMounted,
  onBeforeUnmount,
  watch,
  getCurrentInstance,
} from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { useAlerts } from "@/Composables/useAlerts";
import { useAuditFormatting } from "@/Composables/useAuditFormatting";
import { formatDate } from "@/utils/datetime";
import QuickCreateRecordModal from "@/Pages/Components/Modules/QuickCreateRecordModal.vue";
import AppTooltip from "@/Pages/Components/Globals/AppTooltip.vue";

const props = defineProps({
  module: { type: Object, required: true },
  recordId: { type: String, required: true },
  fields: { type: Array, default: () => [] },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const { success, error: showError } = useAlerts();
const {
  fieldLabel,
  formatValue,
  diffValue,
  dropdownOption,
  isStatusField,
  isBulkChange,
  hasRecordOldValue,
  isLinkChange,
  relatedModuleSingleLabel,
  bulkSummary,
} = useAuditFormatting(props);

const appSettings = usePage().props.appSettings;
const currentUserId = computed(() => usePage().props.auth?.user?.id);

const moduleColor = computed(() =>
  appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color,
);

const activityModules = ref([]);

const COLLAPSE_KEY = "activity-sidebar-hidden";
const collapsed = ref(localStorage.getItem(COLLAPSE_KEY) === "1");
const toggleCollapse = () => {
  collapsed.value = !collapsed.value;
  localStorage.setItem(COLLAPSE_KEY, collapsed.value ? "1" : "0");
};

const toggleTooltip = reactive({
  show: false,
  text: "",
  color: "",
  top: 0,
  left: 0,
});

const onToggleMouseEnter = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();

  toggleTooltip.text = t(
    collapsed.value
      ? "globals.activity_sidebar.expand"
      : "globals.activity_sidebar.collapse",
  );
  toggleTooltip.color = moduleColor.value;
  toggleTooltip.top = rect.top + rect.height / 2;
  toggleTooltip.left = rect.left - 10;
  toggleTooltip.show = true;
};

const onToggleMouseLeave = () => {
  toggleTooltip.show = false;
};

const phase = ref("loading");
const entries = ref([]);

const ACTIVE_TAB_KEY = "activity-sidebar-tab";
const activeTab = ref(localStorage.getItem(ACTIVE_TAB_KEY) ?? "activity");
watch(activeTab, (tab) => {
  localStorage.setItem(ACTIVE_TAB_KEY, tab);
});

const filteredEntries = computed(() => {
  if (activeTab.value === "activity") {
    return entries.value.filter((e) => e.source === "activity");
  }
  if (activeTab.value === "changes") {
    return entries.value.filter((e) => e.source === "audit");
  }
  return entries.value;
});

const loadTimeline = async () => {
  phase.value = "loading";
  try {
    const { data } = await axios.get(
      `/modules/${props.module.slug}/${props.recordId}/timeline`,
    );
    entries.value = data.data;
    activityModules.value = data.activityModules ?? [];
    phase.value = "ready";
  } catch (e) {
    phase.value = "error";
  }
};

onMounted(loadTimeline);
watch(() => props.recordId, loadTimeline);

defineExpose({ loadTimeline });

const quickCreateSlug = ref(null);
const quickCreateMenuOpen = ref(false);
const quickCreateRoot = ref(null);

const openQuickCreate = (slug) => {
  quickCreateSlug.value = slug;
  quickCreateMenuOpen.value = false;
};
const activeQuickCreateModule = computed(
  () =>
    activityModules.value.find((m) => m.slug === quickCreateSlug.value) ?? null,
);

const handleClickOutsideQuickCreate = (event) => {
  if (quickCreateRoot.value && !quickCreateRoot.value.contains(event.target)) {
    quickCreateMenuOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideQuickCreate);
});
onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideQuickCreate);
});

const onActivityCreated = async (created) => {
  const activitySlug = quickCreateSlug.value;
  quickCreateSlug.value = null;

  try {
    await axios.post(
      `/modules/${props.module.slug}/${props.recordId}/relationships/${props.module.slug}_${activitySlug}`,
      { related_ids: [created.id] },
    );
    success(t("globals.activity_sidebar.linked_success"));
    loadTimeline();
  } catch (e) {
    showError(
      e.response?.data?.message || t("globals.activity_sidebar.link_error"),
    );
  }
};

// Tasks render a live checkbox in the timeline toggling it PATCHes the
// real Task record's status directly
const isCompletable = (entry) =>
  entry.source === "activity" &&
  entry.entry_type === "tasks" &&
  "status" in (entry.record ?? {});

const isComplete = (entry) => entry.record?.status === "completed";

const toggleComplete = async (entry) => {
  const nextStatus = isComplete(entry) ? "not_started" : "completed";
  try {
    const { data } = await axios.put(
      `/${entry.entry_type}/${entry.record.id}`,
      { status: nextStatus },
      { headers: { Accept: "application/json" } },
    );
    entry.record.status = data.status;
    entry.record.completed_at = data.completed_at;
  } catch (e) {
    showError(
      e.response?.data?.message || t("globals.activity_sidebar.update_error"),
    );
  }
};

const tz = () => appSettings?.timezone || "UTC";

const relativeTime = (value) => {
  if (!value) return "";
  const date = dayjs(value).tz(tz());
  const now = dayjs().tz(tz());
  const diffMinutes = now.diff(date, "minute");

  if (diffMinutes < 1) return t("globals.activity_sidebar.just_now");
  if (diffMinutes < 60)
    return t("globals.activity_sidebar.minutes_ago", { count: diffMinutes });

  const diffHours = now.diff(date, "hour");
  if (diffHours < 6)
    return t("globals.activity_sidebar.hours_ago", { count: diffHours });
  if (date.isSame(now, "day")) {
    return t("globals.activity_sidebar.today_at", {
      time: date.format("HH:mm"),
    });
  }
  if (date.isSame(now.subtract(1, "day"), "day")) {
    return t("globals.activity_sidebar.yesterday_at", {
      time: date.format("HH:mm"),
    });
  }

  return formatDate(value, appSettings);
};

const dueLabel = (value) => {
  if (!value) return "";
  const date = dayjs(value).tz(tz()).startOf("day");
  const today = dayjs().tz(tz()).startOf("day");
  const diffDays = date.diff(today, "day");

  if (diffDays === 0) return t("globals.activity_sidebar.due_today");
  if (diffDays === 1) return t("globals.activity_sidebar.due_tomorrow");
  if (diffDays > 1)
    return t("globals.activity_sidebar.due_in_days", { count: diffDays });
  if (diffDays === -1) return t("globals.activity_sidebar.overdue");
  return t("globals.activity_sidebar.overdue_by_days", {
    count: Math.abs(diffDays),
  });
};

const ownerPhrase = (entry) => {
  if (!entry.record?.owner_id) return null;
  if (entry.record.owner_id === currentUserId.value) {
    return t("globals.activity_sidebar.assigned_to_you");
  }
  return entry.owner_label
    ? t("globals.activity_sidebar.assigned_to", { name: entry.owner_label })
    : null;
};

const recordUrl = (slug, id) => `/${slug}/${id}`;

const activityTitle = (entry) => {
  if (entry.entry_type === "notes") {
    return entry.record.description || entry.record.name;
  }
  if (entry.entry_type === "calls" && entry.record.duration_minutes) {
    return `${entry.record.name} · ${entry.record.duration_minutes} min`;
  }
  return entry.record.name;
};

const activityMeta = (entry) => {
  const owner = ownerPhrase(entry);

  if (entry.entry_type === "tasks") {
    const parts = [t(entry.module.single_label ?? entry.module.label)];
    if (entry.record.due_at) parts.push(dueLabel(entry.record.due_at));
    if (owner) parts.push(owner);
    return parts.join(" · ");
  }

  const parts = [relativeTime(entry.timestamp)];
  if (owner) parts.push(owner);
  return parts.join(" · ");
};

const isLineItemChange = (entry) =>
  entry.action === "line_item.added" || entry.action === "line_item.removed";

const lineItemTitle = (entry) =>
  t(
    entry.action === "line_item.added"
      ? "globals.activity_sidebar.line_item_added"
      : "globals.activity_sidebar.line_item_removed",
    { name: entry.changes.name },
  );

const lineItemDetail = (entry) =>
  `${entry.changes.quantity} × ${formatValue("total", entry.changes.unit_price)} = ${formatValue("total", entry.changes.total)}`;

const linkTitle = (entry) => {
  const verb = t(`globals.audit_trail.action_labels.${entry.action}`);
  const singleLabel = relatedModuleSingleLabel(entry.changes.related_module);
  return `${verb} ${singleLabel}`;
};

const linkDetail = (entry) =>
  entry.changes.related_label ?? entry.changes.related_id;

const auditIcon = (entry) => {
  if (entry.action === "created") return "fa-solid fa-plus";
  if (entry.action === "deleted") return "fa-solid fa-trash";
  if (entry.action === "linked") return "fa-solid fa-link";
  if (entry.action === "unlinked") return "fa-solid fa-link-slash";
  if (entry.action === "line_item.added") return "fa-solid fa-list-ol";
  if (entry.action === "line_item.removed") return "fa-solid fa-minus";
  if (isBulkChange(entry.changes)) return "fa-solid fa-layer-group";
  if (
    entry.changes &&
    Object.keys(entry.changes).some((f) => isStatusField(f))
  ) {
    return "fa-solid fa-arrows-rotate";
  }
  return "fa-solid fa-pen";
};

const auditFieldTitle = (field) =>
  isStatusField(field)
    ? t("globals.activity_sidebar.field_changed", { field: fieldLabel(field) })
    : t("globals.activity_sidebar.field_updated", { field: fieldLabel(field) });
</script>

<template>
  <div
    class="record-layout__activity-sidebar"
    :class="{ 'record-layout__activity-sidebar--hidden': collapsed }"
  >
    <button
      class="record-layout__activity-sidebar__toggle"
      :style="{ '--module-color': moduleColor }"
      @click="toggleCollapse"
      @mouseenter="onToggleMouseEnter"
      @mouseleave="onToggleMouseLeave"
    >
      <i
        class="fa-solid"
        :class="collapsed ? 'fa-angles-left' : 'fa-angles-right'"
      ></i>
    </button>

    <div class="record-layout__activity-sidebar__panel">
      <div class="activity-sidebar">
        <div class="activity-sidebar__header">
          <div class="activity-sidebar__title">
            {{ $t("globals.activity_sidebar.activity") }}
          </div>

          <div ref="quickCreateRoot" class="activity-sidebar__quick-create">
            <button
              class="activity-sidebar__quick-create__trigger"
              :style="{ '--module-color': moduleColor }"
              @click="quickCreateMenuOpen = !quickCreateMenuOpen"
            >
              <i class="fa-solid fa-plus"></i>
              {{ $t("globals.activity_sidebar.add") }}
            </button>

            <transition name="dropdown-fade">
              <ul
                v-if="quickCreateMenuOpen"
                class="activity-sidebar__quick-create__menu"
              >
                <li
                  v-for="am in activityModules"
                  :key="am.slug"
                  class="activity-sidebar__quick-create__menu__item"
                  @click="openQuickCreate(am.slug)"
                >
                  <span
                    class="activity-sidebar__quick-create__menu__item__icon"
                    :style="{ color: am.color }"
                  >
                    <i :class="am.icon"></i>
                  </span>
                  {{ $t(am.single_label ?? am.label) }}
                </li>
              </ul>
            </transition>
          </div>
        </div>
        <div class="activity-sidebar__tabs">
          <button
            v-for="tab in ['all', 'activity', 'changes']"
            :key="tab"
            class="activity-sidebar__tabs__item"
            :class="{
              'activity-sidebar__tabs__item--active': activeTab === tab,
            }"
            @click="activeTab = tab"
          >
            {{ $t(`globals.activity_sidebar.tabs.${tab}`) }}
          </button>
        </div>

        <div class="activity-sidebar__timeline">
          <div v-if="phase === 'loading'" class="activity-sidebar__loading">
            <i
              class="fa-solid fa-atom fa-spin"
              :style="{ color: moduleColor }"
            ></i>
          </div>
          <div v-else-if="phase === 'error'" class="activity-sidebar__empty">
            {{ $t("globals.activity_sidebar.load_error") }}
          </div>
          <div
            v-else-if="filteredEntries.length === 0"
            class="activity-sidebar__empty"
          >
            {{ $t("globals.activity_sidebar.empty") }}
          </div>

          <ul v-else class="activity-sidebar__list">
            <li
              v-for="(entry, index) in filteredEntries"
              :key="
                entry.source + '-' + (entry.id ?? entry.record?.id ?? index)
              "
              class="activity-sidebar__entry"
            >
              <div class="activity-sidebar__entry__rail">
                <label
                  v-if="isCompletable(entry)"
                  class="activity-sidebar__entry__icon activity-sidebar__entry__icon--checkbox"
                >
                  <input
                    type="checkbox"
                    :checked="isComplete(entry)"
                    @change="toggleComplete(entry)"
                  />
                </label>
                <div
                  v-else-if="entry.source === 'activity'"
                  class="activity-sidebar__entry__icon"
                  :style="{ '--related-color': entry.module.color }"
                >
                  <i :class="entry.module.icon"></i>
                </div>
                <div v-else class="activity-sidebar__entry__icon">
                  <i :class="auditIcon(entry)"></i>
                </div>
              </div>

              <div class="activity-sidebar__entry__body">
                <template v-if="entry.source === 'activity'">
                  <Link
                    :href="recordUrl(entry.module.slug, entry.record.id)"
                    class="activity-sidebar__entry__title activity-sidebar__entry__title--link"
                  >
                    {{ activityTitle(entry) }}
                  </Link>
                  <div class="activity-sidebar__entry__meta">
                    {{ activityMeta(entry) }}
                  </div>
                </template>

                <template v-else>
                  <template v-if="entry.action === 'created'">
                    <div class="activity-sidebar__entry__title">
                      {{ $t("globals.activity_sidebar.record_created") }}
                    </div>
                  </template>

                  <template v-else-if="isLineItemChange(entry)">
                    <div class="activity-sidebar__entry__title">
                      {{ lineItemTitle(entry) }}
                    </div>
                    <div class="activity-sidebar__entry__diff">
                      {{ lineItemDetail(entry) }}
                    </div>
                  </template>

                  <template
                    v-else-if="
                      entry.action === 'deleted' && entry.changes?.record_label
                    "
                  >
                    <div class="activity-sidebar__entry__title">
                      {{
                        $t("globals.audit_trail.deleted_record_label", {
                          name: entry.changes.record_label,
                        })
                      }}
                    </div>
                  </template>

                  <template v-else-if="isLinkChange(entry)">
                    <div class="activity-sidebar__entry__title">
                      {{ linkTitle(entry) }}
                    </div>
                    <Link
                      v-if="entry.changes.related_module && entry.changes.related_id"
                      :href="
                        recordUrl(
                          entry.changes.related_module,
                          entry.changes.related_id,
                        )
                      "
                      class="activity-sidebar__entry__meta activity-sidebar__entry__meta--link"
                    >
                      {{ linkDetail(entry) }}
                    </Link>
                    <div v-else class="activity-sidebar__entry__meta">
                      {{ linkDetail(entry) }}
                    </div>
                  </template>

                  <template v-else-if="isBulkChange(entry.changes)">
                    <div class="activity-sidebar__entry__title">
                      {{ bulkSummary(entry) }}
                    </div>
                    <div
                      v-if="hasRecordOldValue(entry.changes)"
                      class="activity-sidebar__entry__diff"
                    >
                      {{
                        formatValue(
                          entry.changes.field,
                          entry.changes.old_value,
                        )
                      }}
                      <i class="fa-solid fa-arrow-right"></i>
                      {{
                        formatValue(entry.changes.field, entry.changes.value)
                      }}
                    </div>
                  </template>

                  <template
                    v-else-if="
                      entry.changes && Object.keys(entry.changes).length
                    "
                  >
                    <div v-for="(diff, field) in entry.changes" :key="field">
                      <div class="activity-sidebar__entry__title">
                        {{ auditFieldTitle(field) }}
                      </div>
                      <div
                        v-if="isStatusField(field)"
                        class="activity-sidebar__entry__diff"
                      >
                        <span
                          v-if="dropdownOption(field, diff.old)"
                          class="status-badge status-badge--pill status-badge--sm"
                          :style="{
                            color: dropdownOption(field, diff.old).color,
                            backgroundColor: dropdownOption(field, diff.old)
                              .bgColor,
                          }"
                        >
                          {{ $t(dropdownOption(field, diff.old).label) }}
                        </span>
                        <span v-else>{{ formatValue(field, diff.old) }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                        <span
                          v-if="dropdownOption(field, diff.new)"
                          class="status-badge status-badge--pill status-badge--sm"
                          :style="{
                            color: dropdownOption(field, diff.new).color,
                            backgroundColor: dropdownOption(field, diff.new)
                              .bgColor,
                          }"
                        >
                          {{ $t(dropdownOption(field, diff.new).label) }}
                        </span>
                        <span v-else>{{ formatValue(field, diff.new) }}</span>
                      </div>
                      <div v-else class="activity-sidebar__entry__diff">
                        {{ diffValue(field, diff, "old") }}
                        <i class="fa-solid fa-arrow-right"></i>
                        {{ diffValue(field, diff, "new") }}
                      </div>
                    </div>
                  </template>

                  <template v-else>
                    <div class="activity-sidebar__entry__title">
                      {{
                        $t(`globals.audit_trail.action_labels.${entry.action}`)
                      }}
                    </div>
                  </template>

                  <div class="activity-sidebar__entry__meta">
                    {{ relativeTime(entry.timestamp) }} ·
                    {{
                      entry.user?.name ??
                      $t("globals.audit_trail.unknown_actor")
                    }}
                  </div>
                </template>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <QuickCreateRecordModal
      v-if="activeQuickCreateModule"
      :open="!!activeQuickCreateModule"
      :module-slug="activeQuickCreateModule.slug"
      :fields="activeQuickCreateModule.fields ?? []"
      :icon="activeQuickCreateModule.icon"
      :accent-color="activeQuickCreateModule.color"
      @close="quickCreateSlug = null"
      @created="onActivityCreated"
    />

    <AppTooltip
      :show="toggleTooltip.show"
      :text="toggleTooltip.text"
      :top="toggleTooltip.top"
      :left="toggleTooltip.left"
      :color="toggleTooltip.color"
      placement="left"
    />
  </div>
</template>
