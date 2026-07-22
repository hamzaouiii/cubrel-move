<script setup>
import { ref, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import RecordMultiSelectorDrawer from "@/Pages/Components/Modules/RecordMultiSelectorDrawer.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import Text from "@/Pages/Components/FiledTypes/Text.vue";
import Email from "@/Pages/Components/FiledTypes/Email.vue";
import { useAlerts } from "@/Composables/useAlerts";
import { fieldValidation } from "@/utils/fieldValidation";

const { emailValidate } = fieldValidation();
const props = defineProps({
  open: { type: Boolean, default: false },
  meetingId: { type: String, required: true },
  attendee: { type: Object, default: null }, // null = adding new attendee(s)
  existingAttendees: { type: Array, default: () => [] },
  moduleColor: { type: String, default: "var(--module-color)" },
});

const emit = defineEmits(["close", "saved"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error, clearAllAlerts, success } = useAlerts();

const page = usePage();
const allLayouts = computed(() => page.props.layouts);

const attendeeOptions = computed(() => page.props.meetingAttendeeOptions ?? {});
const roleOptions = computed(() => attendeeOptions.value.roles ?? []);
const moduleOptions = computed(
  () => attendeeOptions.value.source_modules ?? [],
);

const internal_sources = computed(() =>
  Object.fromEntries(moduleOptions.value.map((m) => [m.value, m.source_type])),
);
const sourceTypeToSlug = computed(() =>
  Object.fromEntries(moduleOptions.value.map((m) => [m.source_type, m.value])),
);

const external_color = "#94a3b8";
const modules = computed(() => page.props.modules);
const appSettings = page.props.appSettings;
const getModule = (slug) => modules.value.find((m) => m.slug === slug);
const getRelatedColor = (slug) =>
  appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;

const pickerColor = computed(() => getRelatedColor(pickerModule.value));
const colorForSourceType = (sourceType) =>
  sourceType
    ? getRelatedColor(sourceTypeToSlug.value[sourceType])
    : external_color;

const pickerLinkingLayout = computed(() => {
  const layout = allLayouts.value.find((l) => l.module === pickerModule.value);
  return layout?.layouts?.linkingPanel?.columns || null;
});

const initials = (name) => {
  const cleaned = (name || "").replace(/\d+/g, "");
  const words = cleaned.trim().split(/\s+/).filter(Boolean);
  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }
  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
};

const role_icons = {
  organizer: "fa-solid fa-compass-drafting",
  required: "fa-solid fa-circle-check",
  optional: "fa-solid fa-eye",
};
const roleIcon = (value) => role_icons[value] || "fa-solid fa-user";

const pickerMode = ref("internal");
const pickerModule = ref("contacts");
const multiSelectOpen = ref(false);
const pendingAttendees = ref([]);
const saving = ref(false);
const rowErrors = ref({});

const emptyRow = () => ({
  source_type: null,
  source_id: null,
  name: "",
  email: "",
  role: "required",
});

const row = ref(emptyRow());

const emptyGuest = () => ({ name: "", email: "", role: "required" });
const externalForm = ref(emptyGuest());
const externalFormErrors = ref({});

const addExternalGuest = () => {
  const errors = {};

  if (!externalForm.value.name?.trim()) {
    errors.name = "modules.meeting_attendees.errors.name_required";
  }
  if (!externalForm.value.email?.trim()) {
    errors.email = "modules.meeting_attendees.errors.email_required";
  }
  if (!emailValidate(externalForm.value.email?.trim())) {
    error(t("modules.meeting_attendees.errors.email_invalid"));
    errors.email = "modules.meeting_attendees.errors.email_invalid";
  }

  externalFormErrors.value = errors;
  if (Object.keys(errors).length) return;

  pendingAttendees.value.push({
    source_type: null,
    source_id: null,
    name: externalForm.value.name,
    email: externalForm.value.email,
    role: externalForm.value.role,
  });
  externalForm.value = emptyGuest();
};

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) return;
    rowErrors.value = {};
    pendingAttendees.value = [];
    externalForm.value = emptyGuest();
    externalFormErrors.value = {};
    if (props.attendee) {
      row.value = { ...props.attendee };
      pickerMode.value = props.attendee.source_type ? "internal" : "external";
    } else {
      row.value = emptyRow();
      pickerMode.value = "internal";
      pickerModule.value = "contacts";
    }
  },
);

const excludeIds = computed(() => {
  const sourceType = internal_sources.value[pickerModule.value];
  return [
    ...props.existingAttendees
      .filter((a) => a.source_type === sourceType)
      .map((a) => a.source_id),
    ...pendingAttendees.value
      .filter((a) => a.source_type === sourceType)
      .map((a) => a.source_id),
  ];
});

const onRecordsSelected = (records) => {
  const sourceType = internal_sources.value[pickerModule.value];
  records.forEach((record) => {
    pendingAttendees.value.push({
      source_type: sourceType,
      source_id: record.id,
      name: record.name ?? "",
      email: record.email ?? "",
      role: "required",
    });
  });
};

const removePending = (index) => {
  pendingAttendees.value.splice(index, 1);
};

const setPendingRole = (pending, value) => {
  if (value === "organizer") {
    pendingAttendees.value.forEach((p) => {
      if (p !== pending && p.role === "organizer") p.role = "required";
    });
  }
  pending.role = value;
};

const canSave = computed(() => {
  if (saving.value) return false;
  if (props.attendee?.id) return !!row.value.name?.trim();
  return pendingAttendees.value.length > 0;
});

const validateRow = () => {
  if (!props.attendee) return true;

  const errors = {};
  if (!row.value.name?.trim()) {
    errors.name = "modules.meeting_attendees.errors.name_required";
  }
  rowErrors.value = errors;
  return Object.keys(errors).length === 0;
};

const close = () => emit("close");

const saveRow = async () => {
  clearAllAlerts();
  if (!validateRow()) {
    error(t("modules.meeting_attendees.errors.has_errors"));
    return;
  }

  saving.value = true;
  try {
    if (props.attendee?.id) {
      const { data } = await axios.put(
        `/meeting-attendees/${props.attendee.id}`,
        {
          name: row.value.name,
          email: row.value.email || null,
          role: row.value.role,
        },
      );
      success(t("modules.meeting_attendees.save_success"));
      emit("saved", data);
      close();
      return;
    }

    const remaining = [];
    for (const pending of pendingAttendees.value) {
      try {
        const { data } = await axios.post("/meeting-attendees", {
          meeting_id: props.meetingId,
          source_type: pending.source_type,
          source_id: pending.source_id,
          name: pending.name,
          email: pending.email || null,
          role: pending.role,
        });
        emit("saved", data);
      } catch (e) {
        remaining.push(pending);
        console.error("AttendeeOverlay batch save error", e.message);
      }
    }
    pendingAttendees.value = remaining;

    if (remaining.length) {
      error(t("modules.meeting_attendees.errors.has_errors"));
    } else {
      success(t("modules.meeting_attendees.save_success"));
      close();
    }
  } catch (e) {
    error(
      e.response?.data?.message ||
        t("modules.meeting_attendees.errors.has_errors"),
    );
    console.error("AttendeeOverlay save error", e.message);
  } finally {
    saving.value = false;
  }
};

const canAddGuest = computed(
  () => !!externalForm.value.name?.trim() && !!externalForm.value.email?.trim(),
);
</script>

<template>
  <Transition name="slide-right" appear>
    <div
      v-if="open"
      class="record-overlay attendees-panel"
      :style="{ '--related-color': moduleColor, '--module-color': moduleColor }"
      role="dialog"
      aria-modal="true"
      @click.self="close"
    >
      <div class="related-links related-links--compact">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{
              attendee
                ? $t("modules.meeting_attendees.edit_attendee")
                : $t("modules.meeting_attendees.add_attendee")
            }}
          </div>
          <button class="related-links__header__close" @click="close">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="attendees-panel__body">
          <div
            class="record-layout__sections__item__layout__field"
            v-if="!attendee"
          >
            <span class="record-layout__sections__item__layout__field__label">
              {{ $t("modules.meeting_attendees.source_label") }}
            </span>
            <div class="attendees-panel__type-cards">
              <button
                class="attendees-panel__type-card"
                :class="{
                  'attendees-panel__type-card--active':
                    pickerMode === 'internal',
                }"
                @click="pickerMode = 'internal'"
              >
                <span class="attendees-panel__type-card__icon">
                  <i class="fa-solid fa-user-group"></i>
                </span>
                <span class="attendees-panel__type-card__text">
                  <span class="attendees-panel__type-card__label">
                    {{ $t("modules.meeting_attendees.internal") }}
                  </span>
                  <span class="attendees-panel__type-card__hint">
                    {{ $t("modules.meeting_attendees.internal_hint") }}
                  </span>
                </span>
              </button>
              <button
                class="attendees-panel__type-card"
                :class="{
                  'attendees-panel__type-card--active':
                    pickerMode === 'external',
                }"
                @click="pickerMode = 'external'"
              >
                <span class="attendees-panel__type-card__icon">
                  <i class="fa-solid fa-globe"></i>
                </span>
                <span class="attendees-panel__type-card__text">
                  <span class="attendees-panel__type-card__label">
                    {{ $t("modules.meeting_attendees.external_guest") }}
                  </span>
                  <span class="attendees-panel__type-card__hint">
                    {{ $t("modules.meeting_attendees.external_hint") }}
                  </span>
                </span>
              </button>
            </div>
          </div>

          <template v-if="pickerMode === 'internal' && !attendee">
            <div class="record-layout__sections__item__layout">
              <div
                class="record-layout__sections__item__layout__field"
                :style="{ '--module-color': pickerColor }"
              >
                <span
                  class="record-layout__sections__item__layout__field__label"
                >
                  {{ $t("modules.meeting_attendees.select_type") }}
                </span>
                <Select
                  v-model="pickerModule"
                  mode="edit"
                  :nullable="true"
                  :dropdown_list="{ values: moduleOptions }"
                />
              </div>
              <div
                class="record-layout__sections__item__layout__field"
                :style="{ '--module-color': pickerColor }"
              >
                <span
                  class="record-layout__sections__item__layout__field__label"
                >
                  {{ $t("modules.meeting_attendees.select_record") }}
                </span>
                <div
                  class="attendees-panel__search-trigger"
                  @click="multiSelectOpen = true"
                >
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <span>
                    {{
                      $t("modules.meeting_attendees.search_placeholder", {
                        label: $t(`modules.${pickerModule}.label`),
                      })
                    }}
                  </span>
                  <i
                    class="fa-solid fa-chevron-right attendees-panel__search-trigger__chevron"
                  ></i>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="pickerMode === 'external' && !attendee">
            <div class="attendees-panel__guest-block">
              <div class="record-layout__sections__item__layout__field">
                <span
                  class="record-layout__sections__item__layout__field__label"
                  :class="{
                    'record-layout__sections__item__layout__field__label--error':
                      externalFormErrors.name,
                  }"
                >
                  {{ $t("modules.meeting_attendees.fields.name") }}
                  <span class="attendees-panel__required">*</span>
                </span>
                <Text
                  v-model="externalForm.name"
                  mode="edit"
                  :has-error="!!externalFormErrors.name"
                />
              </div>
              <div class="record-layout__sections__item__layout__field">
                <span
                  class="record-layout__sections__item__layout__field__label"
                  :class="{
                    'record-layout__sections__item__layout__field__label--error':
                      externalFormErrors.email,
                  }"
                >
                  {{ $t("modules.meeting_attendees.fields.email") }}
                  <span class="attendees-panel__required">*</span>
                </span>
                <Email
                  v-model="externalForm.email"
                  mode="edit"
                  :has-error="!!externalFormErrors.email"
                />
              </div>
              <div class="record-layout__sections__item__layout__field">
                <span
                  class="record-layout__sections__item__layout__field__label"
                >
                  {{ $t("modules.meeting_attendees.fields.role") }}
                </span>
                <div class="attendees-panel__role-cards">
                  <button
                    v-for="opt in roleOptions"
                    :key="opt.value"
                    class="attendees-panel__type-card"
                    :class="{
                      'attendees-panel__type-card--active':
                        externalForm.role === opt.value,
                    }"
                    @click="externalForm.role = opt.value"
                  >
                    <span class="attendees-panel__type-card__icon">
                      <i :class="roleIcon(opt.value)"></i>
                    </span>
                    <span class="attendees-panel__type-card__text">
                      <span class="attendees-panel__type-card__label">
                        {{ $t(opt.label) }}
                      </span>
                    </span>
                  </button>
                </div>
              </div>
              <div class="attendees-panel__add-guest">
                <button @click="addExternalGuest" :disabled="!canAddGuest">
                  <i class="fa-solid fa-plus"></i>
                  {{ $t("modules.meeting_attendees.add_guest") }}
                </button>
              </div>
            </div>
          </template>

          <div
            v-if="!attendee && pendingAttendees.length"
            class="attendees-panel__pending-list"
          >
            <div
              v-for="(pending, index) in pendingAttendees"
              :key="index"
              class="attendees-panel__pending-chip"
              :style="{
                '--module-color': colorForSourceType(pending.source_type),
              }"
            >
              <div class="attendees-panel__avatar">
                {{ initials(pending.name) }}
              </div>
              <div class="attendees-panel__attendee-info">
                <div class="attendees-panel__attendee-info__name">
                  {{ pending.name }}
                </div>
                <div
                  v-if="pending.email"
                  class="attendees-panel__attendee-info__meta"
                >
                  {{ pending.email }}
                </div>
                <div class="attendees-panel__pending-chip__role">
                  <button
                    v-for="opt in roleOptions"
                    :key="opt.value"
                    class="attendees-panel__pending-chip__role-btn"
                    :class="{
                      'attendees-panel__pending-chip__role-btn--active':
                        pending.role === opt.value,
                    }"
                    @click="setPendingRole(pending, opt.value)"
                  >
                    {{ $t(opt.label) }}
                  </button>
                </div>
              </div>
              <button
                class="attendees-panel__pending-chip__remove"
                @click="removePending(index)"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>

          <template v-if="attendee && !row.source_type">
            <div class="record-layout__sections__item__layout__field">
              <span
                class="record-layout__sections__item__layout__field__label"
                :class="{
                  'record-layout__sections__item__layout__field__label--error':
                    rowErrors.name,
                }"
              >
                {{ $t("modules.meeting_attendees.fields.name") }}
                <span class="attendees-panel__required">*</span>
              </span>
              <Text
                v-model="row.name"
                mode="edit"
                :has-error="!!rowErrors.name"
              />
            </div>
            <div class="record-layout__sections__item__layout__field">
              <span class="record-layout__sections__item__layout__field__label">
                {{ $t("modules.meeting_attendees.fields.email") }}
              </span>
              <Email v-model="row.email" mode="edit" />
            </div>
          </template>

          <div
            v-if="attendee && row.source_type"
            class="record-layout__sections__item__layout__field"
          >
            <span class="record-layout__sections__item__layout__field__label">
              {{ $t("modules.meeting_attendees.fields.name") }}
            </span>
            <div class="attendees-panel__static-value">{{ row.name }}</div>
          </div>

          <div
            v-if="attendee"
            class="record-layout__sections__item__layout__field"
          >
            <span class="record-layout__sections__item__layout__field__label">
              {{ $t("modules.meeting_attendees.fields.role") }}
            </span>
            <div class="attendees-panel__role-cards">
              <button
                v-for="opt in roleOptions"
                :key="opt.value"
                class="attendees-panel__type-card"
                :class="{
                  'attendees-panel__type-card--active': row.role === opt.value,
                }"
                @click="row.role = opt.value"
              >
                <span class="attendees-panel__type-card__icon">
                  <i :class="roleIcon(opt.value)"></i>
                </span>
                <span class="attendees-panel__type-card__text">
                  <span class="attendees-panel__type-card__label">
                    {{ $t(opt.label) }}
                  </span>
                </span>
              </button>
            </div>
          </div>
        </div>

        <div class="related-links__footer">
          <span
            v-if="!attendee && pendingAttendees.length"
            class="attendees-panel__footer-count"
          >
            <i class="fa-solid fa-users"></i>
            {{
              $t("modules.meeting_attendees.selected_count", {
                count: pendingAttendees.length,
              })
            }}
          </span>
          <button
            class="related-links__footer__btn related-links__footer__btn--cancel"
            @click="close"
          >
            {{ $t("modules.actions.cancel") }}
          </button>
          <button
            class="related-links__footer__btn related-links__footer__btn--primary"
            :disabled="!canSave"
            @click="saveRow"
          >
            <i v-if="saving" class="fa-solid fa-atom fa-spin"></i>
            {{ $t("modules.actions.save") }}
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <RecordMultiSelectorDrawer
    :open="multiSelectOpen"
    :search-endpoint="`/relatedfield/search/${pickerModule}`"
    :related-module="pickerModule"
    :accent-color="pickerColor"
    :layout="pickerLinkingLayout"
    :exclude-ids="excludeIds"
    @select="onRecordsSelected"
    @close="multiSelectOpen = false"
  />
</template>
