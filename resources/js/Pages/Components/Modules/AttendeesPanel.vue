<script setup>
import { ref, computed, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import AttendeeOverlay from "@/Pages/Components/Modules/AttendeeOverlay.vue";
import { useConfirm } from "@/Composables/useConfirm";

const props = defineProps({
  meetingId: { type: String, required: true },
  moduleColor: { type: String, default: "var(--module-color)" },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { confirm } = useConfirm();

const page = usePage();

const attendeeOptions = computed(() => page.props.meetingAttendeeOptions ?? {});
const rsvpOptions = computed(() => attendeeOptions.value.rsvp_statuses ?? []);
const attendanceOptions = computed(
  () => attendeeOptions.value.attendance_statuses ?? [],
);
const sourceModules = computed(
  () => attendeeOptions.value.source_modules ?? [],
);
const roleOptions = computed(() => attendeeOptions.value.roles ?? []);

const external_color = "#94a3b8";
const modules = computed(() => page.props.modules);
const appSettings = page.props.appSettings;
const getModule = (slug) => modules.value.find((m) => m.slug === slug);
const getRelatedColor = (slug) =>
  appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;

const sourceTypeToSlug = computed(() =>
  Object.fromEntries(sourceModules.value.map((m) => [m.source_type, m.value])),
);
const attendeeColor = (item) =>
  item.source_type
    ? getRelatedColor(sourceTypeToSlug.value[item.source_type])
    : external_color;

const items = ref([]);
const loading = ref(false);

const fetchItems = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get("/meeting-attendees", {
      params: { meeting_id: props.meetingId },
    });
    items.value = data;
  } catch (e) {
    console.error("AttendeesPanel fetch error", e.message);
  } finally {
    loading.value = false;
  }
};
fetchItems();

const roleRank = computed(() =>
  Object.fromEntries(roleOptions.value.map((opt, index) => [opt.value, index])),
);
const sortedItems = computed(() =>
  [...items.value].sort((a, b) => {
    const rankDiff =
      (roleRank.value[a.role] ?? 99) - (roleRank.value[b.role] ?? 99);
    return rankDiff !== 0 ? rankDiff : a.name.localeCompare(b.name);
  }),
);

const rsvpSummary = computed(() => {
  const counts = { accepted: 0, declined: 0, tentative: 0, invited: 0 };
  items.value.forEach((i) => {
    if (counts[i.rsvp_status] !== undefined) counts[i.rsvp_status]++;
  });
  return counts;
});

const attendanceSummary = computed(() => {
  const counts = { attended: 0, no_show: 0 };
  items.value.forEach((i) => {
    if (counts[i.attendance_status] !== undefined)
      counts[i.attendance_status]++;
  });
  return counts;
});

const hasUnrecordedAttendance = computed(() =>
  items.value.some((i) => !i.attendance_status),
);

const initials = (name) => {
  const cleaned = (name || "").replace(/\d+/g, "");
  const words = cleaned.trim().split(/\s+/).filter(Boolean);
  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }
  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
};

const overlayOpen = ref(false);
const activeAttendee = ref(null);

const openNewRow = () => {
  activeAttendee.value = null;
  overlayOpen.value = true;
};

const openEditRow = (item) => {
  activeAttendee.value = item;
  overlayOpen.value = true;
};

const handleSaved = (attendee) => {
  const idx = items.value.findIndex((i) => i.id === attendee.id);
  if (idx !== -1) items.value[idx] = attendee;
  else items.value.push(attendee);
};

const deleteRow = async (item) => {
  const ok = await confirm({
    title: t("modules.meeting_attendees.delete_title"),
    message: t("modules.meeting_attendees.delete_confirm"),
    confirmText: t("modules.meeting_attendees.delete_yes"),
    cancelText: t("modules.meeting_attendees.delete_no"),
    danger: true,
  });
  if (!ok) return;

  try {
    await axios.delete(`/meeting-attendees/${item.id}`);
    items.value = items.value.filter((i) => i.id !== item.id);
  } catch (e) {
    console.error("AttendeesPanel delete error", e.message);
  }
};

const editingRowId = ref(null);
const statusDraft = ref({ rsvp_status: null, attendance_status: null });

const isRowEditing = (item) => editingRowId.value === item.id;

const toggleRowEdit = (item) => {
  if (editingRowId.value === item.id) {
    editingRowId.value = null;
    return;
  }
  editingRowId.value = item.id;
  statusDraft.value = {
    rsvp_status: item.rsvp_status,
    attendance_status: item.attendance_status,
  };
};

const updateDraftStatus = (field, value) => {
  if (field === "rsvp_status" && value === null) return;
  statusDraft.value[field] = value;
};

const saveRowStatus = async (item) => {
  try {
    const { data } = await axios.put(`/meeting-attendees/${item.id}`, {
      rsvp_status: statusDraft.value.rsvp_status,
      attendance_status: statusDraft.value.attendance_status,
    });
    const idx = items.value.findIndex((i) => i.id === item.id);
    if (idx !== -1) items.value[idx] = data;
  } catch (e) {
    console.error("AttendeesPanel status update error", e.message);
  } finally {
    editingRowId.value = null;
  }
};

const markAllAttended = async () => {
  const ok = await confirm({
    title: t("modules.meeting_attendees.mark_all_attended"),
    message: t("modules.meeting_attendees.mark_all_attended"),
    confirmText: t("modules.meeting_attendees.mark_all_attended"),
    cancelText: t("modules.meeting_attendees.delete_no"),
  });
  if (!ok) return;

  try {
    await axios.post("/meeting-attendees/mark-all-attended", {
      meeting_id: props.meetingId,
    });
    items.value.forEach((i) => {
      if (!i.attendance_status) i.attendance_status = "attended";
    });
  } catch (e) {
    console.error("AttendeesPanel mark-all-attended error", e.message);
  }
};
</script>

<template>
  <div class="attendees-panel" :style="{ '--module-color': moduleColor }">
    <div class="attendees-panel__header">
      <div class="attendees-panel__header__summary" v-if="items.length">
        {{ items.length }} {{ $t("modules.meeting_attendees.summary.invited") }}
        ·
        {{ rsvpSummary.accepted }}
        {{ $t("modules.meeting_attendees.summary.accepted") }}
        ·
        {{ attendanceSummary.attended }}
        {{ $t("modules.meeting_attendees.summary.attended") }}
      </div>
      <div class="attendees-panel__header__actions">
        <button
          v-if="items.length && hasUnrecordedAttendance"
          class="attendees-panel__header__mark-all"
          @click="markAllAttended"
        >
          <i class="fa-solid fa-check-double"></i>
          {{ $t("modules.meeting_attendees.mark_all_attended") }}
        </button>
        <button class="attendees-panel__header__add" @click="openNewRow">
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
    </div>

    <div v-if="loading" class="attendees-panel__empty">
      <i class="fa-solid fa-atom fa-spin"></i>
    </div>

    <div v-else-if="!items.length" class="attendees-panel__empty">
      <i class="fa-solid fa-users"></i>
      <span>{{ $t("modules.meeting_attendees.no_attendees") }}</span>
    </div>

    <div v-else class="attendees-panel__table-wrap">
      <table class="attendees-panel__table">
        <thead>
          <tr>
            <th>{{ $t("modules.meeting_attendees.fields.attendee") }}</th>
            <th>{{ $t("modules.meeting_attendees.fields.rsvp_status") }}</th>
            <th>
              {{ $t("modules.meeting_attendees.fields.attendance_status") }}
            </th>
            <th class="attendees-panel__col-actions"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in sortedItems"
            :key="item.id"
            class="attendees-panel__row"
            :style="{ '--module-color': attendeeColor(item) }"
          >
            <td @click="openEditRow(item)">
              <div class="attendees-panel__attendee">
                <div class="attendees-panel__avatar">
                  {{ initials(item.name) }}
                </div>
                <div class="attendees-panel__attendee-info">
                  <div class="attendees-panel__attendee-info__name">
                    {{ item.name }}
                    <span
                      v-if="!item.source_type"
                      class="attendees-panel__badge attendees-panel__badge--external"
                    >
                      {{ $t("modules.meeting_attendees.external") }}
                    </span>
                  </div>
                  <div class="attendees-panel__attendee-info__meta">
                    {{ $t(`modules.meeting_attendees.roles.${item.role}`) }}
                    <template v-if="item.email"> · {{ item.email }}</template>
                  </div>
                </div>
              </div>
            </td>
            <td class="attendees-panel__col-status" @click.stop>
              <Select
                v-if="isRowEditing(item)"
                :model-value="statusDraft.rsvp_status"
                mode="dashboard"
                :nullable="true"
                :searchable="false"
                :dropdown_list="{ values: rsvpOptions }"
                @update:model-value="
                  (val) => updateDraftStatus('rsvp_status', val)
                "
              />
              <span
                v-else
                class="attendees-panel__pill"
                :class="`attendees-panel__pill--${item.rsvp_status}`"
              >
                {{
                  $t(
                    `modules.meeting_attendees.rsvp_statuses.${item.rsvp_status}`,
                  )
                }}
              </span>
            </td>
            <td class="attendees-panel__col-status" @click.stop>
              <Select
                v-if="isRowEditing(item)"
                :model-value="statusDraft.attendance_status"
                mode="dashboard"
                :nullable="true"
                :searchable="false"
                :dropdown_list="{ values: attendanceOptions }"
                @update:model-value="
                  (val) => updateDraftStatus('attendance_status', val)
                "
              />
              <span
                v-else
                class="attendees-panel__pill"
                :class="`attendees-panel__pill--${item.attendance_status || 'none'}`"
              >
                {{
                  item.attendance_status
                    ? $t(
                        `modules.meeting_attendees.attendance_statuses.${item.attendance_status}`,
                      )
                    : "—"
                }}
              </span>
            </td>
            <td
              class="attendees-panel__col-actions"
              :class="{
                'attendees-panel__col-actions--visible': isRowEditing(item),
              }"
              @click.stop
            >
              <button
                class="attendees-panel__btn-icon"
                :class="{
                  'attendees-panel__btn-icon--save': isRowEditing(item),
                }"
                @click="
                  isRowEditing(item) ? saveRowStatus(item) : toggleRowEdit(item)
                "
              >
                <i
                  :class="
                    isRowEditing(item) ? 'fa-solid fa-check' : 'fa-solid fa-pen'
                  "
                ></i>
              </button>
              <button
                class="attendees-panel__btn-delete"
                @click="deleteRow(item)"
              >
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <AttendeeOverlay
      :open="overlayOpen"
      :meeting-id="meetingId"
      :attendee="activeAttendee"
      :existing-attendees="items"
      :module-color="moduleColor"
      @close="overlayOpen = false"
      @saved="handleSaved"
    />
  </div>
</template>
