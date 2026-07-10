<script setup>
import { usePage, useForm } from "@inertiajs/vue3";

import {
  computed,
  ref,
  getCurrentInstance,
  onBeforeUnmount,
  onMounted,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useDragReorder } from "@/Composables/useDragReorder";
import StatusOptionRowFields from "@/Pages/Components/Settings/Dropdowns/StatusOptionRowFields.vue";
import StatusBadgePreview from "@/Pages/Components/Settings/Dropdowns/StatusBadgePreview.vue";

const { error, warning, success, info, clearAllAlerts } = useAlerts();

const emit = defineEmits(["onCloseModal"]);

const props = defineProps({
  dropdown: Object,
  isStatus: {
    type: Boolean,
    default: false,
  },
});
const appSettings = usePage().props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const newItem = useForm({
  label: "",
  value: "",
  color: "#374151",
  bgColor: "#e5e7eb",
  icon: "",
});

const form = useForm({
  key: props.dropdown?.key || "",
  values: props.dropdown?.values || {},
  is_status: props.dropdown?.is_status ?? props.isStatus,
});

// The list's own persisted flag is the source of truth once it exists;
// the field-context prop is only a fallback for lists saved before this
// flag existed (or lists still being created for the first time).
const isStatusMode = computed(() => form.is_status);

// Rows already saved on the server when the modal opened — their generated
// `value` (system key) must never be silently rewritten by later label edits,
// since other records may already reference it.
const existingItems = new WeakSet(
  Array.isArray(form.values) ? form.values : [],
);

// Stable v-for key per row, independent of `value` (which changes on every
// keystroke while typing a new row's label) — keying on `value` would make
// Vue tear down and recreate the input on each character, losing focus.
const rowKeys = new WeakMap();
let rowKeySeq = 0;
const rowKey = (item) => {
  if (!rowKeys.has(item)) {
    rowKeys.set(item, `row-${rowKeySeq++}`);
  }
  return rowKeys.get(item);
};

// Already-saved status rows render collapsed (label/value only, pen+bin to
// edit/delete) so a long list doesn't turn into a wall of color pickers;
// a freshly-added blank row opens expanded since there's nothing to collapse.
const expandedRows = ref(new Set());
const isExpanded = (item) => expandedRows.value.has(rowKey(item));
const toggleExpand = (item) => {
  const key = rowKey(item);
  const next = new Set(expandedRows.value);
  if (next.has(key)) {
    next.delete(key);
  } else {
    next.add(key);
  }
  expandedRows.value = next;
};

const generatedSystemvalue = (label) => {
  if (!label) return "";
  const value = label
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return value;
};

const rowIsDirty = computed(() => {
  return newItem.label.length >= 3;
});

const valueExistsError = ref(false);

const addItem = () => {
  if (!newItem.isDirty) return;
  if (
    form.values.some(
      (item) => item.value === generatedSystemvalue(newItem.value.label),
    )
  ) {
    error(t("dropdowns.value_already_exists"));
    valueExistsError.value = true;
    return;
  }
  const item = {
    label: newItem.label,
    value: generatedSystemvalue(newItem.label),
  };
  if (isStatusMode.value) {
    item.color = newItem.color;
    item.bgColor = newItem.bgColor;
    item.icon = newItem.icon;
  }
  form.values.push(item);
  newItem.reset();
  valueExistsError.value = false;
};

const deleteItem = (index) => {
  const values = [...form.values];
  values.splice(index, 1);
  form.values = values;
};

// Status rows are edited in place — "+" just appends a blank, already-live
// row instead of staging one behind a separate commit step.
const addBlankRow = () => {
  form.values.push({ label: "", value: "" });
  // Re-read the pushed row through the reactive array instead of using the
  // local object literal — Vue wraps array elements in a reactive proxy on
  // access, so keying off the raw pre-push object would never match what
  // the template's v-for (and thus isExpanded/rowKey) actually sees.
  const pushed = form.values[form.values.length - 1];
  expandedRows.value = new Set([...expandedRows.value, rowKey(pushed)]);
};

const onLabelInput = (item) => {
  if (!existingItems.has(item)) {
    item.value = generatedSystemvalue(item.label);
  }
};

const isDuplicateValue = (item, index) => {
  if (!item.value) return false;
  return form.values.some(
    (other, i) => i !== index && other.value === item.value,
  );
};

const {
  dragIndex,
  dragOverZone,
  ghostWidth,
  ghostHeight,
  originOffset,
  ghostRenderPos,
  startDrag,
  setZoneOver,
  onGlobalDragOver,
  endDrag,
  dropAtZone,
} = useDragReorder();

const onDragStart = (index, event) => {
  if (!isStatusMode.value) return;
  startDrag(index, event);
};

const onZoneDragOver = (zoneIndex, event) => {
  if (!isStatusMode.value) return;
  setZoneOver(zoneIndex, event);
};

const onZoneDrop = (zoneIndex) => {
  if (!isStatusMode.value || dragIndex.value === null) return;
  form.values = dropAtZone(form.values, zoneIndex);
  endDrag();
};

const onDragEnd = () => {
  endDrag();
};

const listIsDirty = computed(() => {
  return form.isDirty || (!isStatusMode.value && rowIsDirty.value);
});

const resetList = () => {
  warning("Resetting List to original values ");
  form.reset();
  newItem.reset();
  valueExistsError.value = false;
};

const saveList = () => {
  if (!isStatusMode.value && rowIsDirty.value) {
    addItem();
  }
  if (form.isDirty) {
    info(t("modules.actions.saving"));
    form.put("/settings/dropdowns/" + props.dropdown.id, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.dropdown.update_success"));
        closeModalClicked();
      },
      onError: (e) => {
        clearAllAlerts();
        error(t("settings.dropdown.save_error"));
        console.error(e);
      },
    });
  }
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    saveList();
  }
}
const closeModalClicked = () => {
  emit("onCloseModal");
};
onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
  <div class="dropdown-list-modal" @dragover="onGlobalDragOver">
    <div class="dropdown-list-modal__close" @click="closeModalClicked">
      <i class="fa-solid fa-xmark"></i>
    </div>
    <div class="dropdown-list-modal__container">
      <div
        class="settings"
        :style="{ '--primary-color': appSettings.primary_color }"
      >
        <div class="settings__dropdown">
          <div class="settings__dropdown__edit">
            <div class="settings__dropdown__edit__header">
              <ul class="settings__dropdown__edit__header__info">
                <li class="settings__dropdown__edit__header__info__data">
                  <span
                    class="settings__dropdown__edit__header__info__data__label"
                    >{{ $t("settings.dropdown.list_name") }}:</span
                  >
                  <span
                    class="settings__dropdown__edit__header__info__data__value"
                    >{{ dropdown.key }}</span
                  >
                </li>
                <li
                  class="settings__dropdown__edit__header__info__data"
                  v-if="dropdown.field_key"
                >
                  <span
                    class="settings__dropdown__edit__header__info__data__label"
                    >{{ $t("settings.dropdown.related_field") }}:
                  </span>
                  <span
                    class="settings__dropdown__edit__header__info__data__value"
                    >{{ dropdown.field_key }}</span
                  >
                </li>
                <li class="settings__dropdown__edit__header__info__indicator">
                  <span>{{ $t("settings.dropdown.display_label") }}</span>
                  <span>{{ $t("settings.dropdown.value") }}</span>

                  <div></div>
                </li>
              </ul>
            </div>
            <ul>
              <li
                v-if="isStatusMode"
                class="status-drop-zone"
                :class="{
                  'status-drop-zone--active':
                    dragIndex !== null && dragOverZone === 0,
                }"
                @dragover="onZoneDragOver(0, $event)"
                @drop="onZoneDrop(0)"
              ></li>
              <template
                v-for="(l, index) in form.values"
                :key="rowKey(l)"
              >
              <li
                class="settings__dropdown__edit__value"
                :class="{
                  'settings__dropdown__edit__value--status': isStatusMode,
                  'settings__dropdown__edit__value--dragging':
                    dragIndex === index,
                }"
                :draggable="isStatusMode ? 'true' : 'false'"
                @dragstart="onDragStart(index, $event)"
                @dragend="onDragEnd"
              >
                <div class="settings__dropdown__edit__value__row">
                  <span
                    v-if="isStatusMode"
                    class="settings__dropdown__edit__value__drag-handle"
                  >
                    <i class="fa-solid fa-grip-vertical"></i>
                  </span>

                  <div
                    v-if="isStatusMode"
                    class="settings__dropdown__edit__value__actions settings__dropdown__edit__value__actions--left"
                  >
                    <span
                      class="settings__dropdown__edit__value__actions__edit"
                      @click="toggleExpand(l)"
                    >
                      <i
                        :class="
                          isExpanded(l) ? 'fa-solid fa-chevron-up' : 'fa-solid fa-pen'
                        "
                      ></i>
                    </span>
                    <span
                      class="settings__dropdown__edit__value__actions__delete"
                      @click="deleteItem(index)"
                    >
                      <i class="fa-solid fa-trash-can"></i>
                    </span>
                  </div>

                  <div class="settings__dropdown__edit__value__item">
                    <input
                      v-if="isStatusMode && isExpanded(l)"
                      type="text"
                      v-model="l.label"
                      :placeholder="$t('settings.dropdown.display_label')"
                      @input="onLabelInput(l)"
                    />
                    <StatusBadgePreview
                      v-else-if="isStatusMode"
                      :label="l.label"
                      :color="l.color"
                      :bg-color="l.bgColor"
                      :icon="l.icon"
                    />
                    <span v-else>{{ $t(l.label) }}</span>
                  </div>

                  <div class="settings__dropdown__edit__value__item">
                    <input
                      v-if="isStatusMode && isExpanded(l)"
                      type="text"
                      :value="l.value"
                      :class="{ error: isDuplicateValue(l, index) }"
                      readonly
                      disabled
                    />
                    <span v-else>{{ $t(l.value) }}</span>
                  </div>

                  <div
                    v-if="!isStatusMode"
                    class="settings__dropdown__edit__value__actions"
                  >
                    <span
                      class="settings__dropdown__edit__value__actions__delete"
                      @click="deleteItem(index)"
                    >
                      <i class="fa-solid fa-trash-can"></i>
                    </span>
                  </div>
                </div>

                <div
                  v-if="isStatusMode && isExpanded(l)"
                  class="settings__dropdown__edit__value__style"
                >
                  <StatusOptionRowFields
                    v-model:color="l.color"
                    v-model:bg-color="l.bgColor"
                    v-model:icon="l.icon"
                    :label="l.label"
                  />
                </div>
              </li>
              <li
                v-if="isStatusMode"
                class="status-drop-zone"
                :class="{
                  'status-drop-zone--active':
                    dragIndex !== null && dragOverZone === index + 1,
                }"
                @dragover="onZoneDragOver(index + 1, $event)"
                @drop="onZoneDrop(index + 1)"
              ></li>
              </template>
              <li
                v-if="isStatusMode"
                class="settings__dropdown__edit__value settings__dropdown__edit__value--add"
                @click="addBlankRow"
              >
                <i class="fa-solid fa-circle-plus"></i>
                <span>{{ $t("settings.dropdown.add_option") }}</span>
              </li>
              <li v-else class="settings__dropdown__edit__value">
                <div class="settings__dropdown__edit__value__row">
                  <div class="settings__dropdown__edit__value__item">
                    <input
                      type="text"
                      v-model="newItem.label"
                      @keyup.enter="addItem"
                    />
                  </div>
                  <div class="settings__dropdown__edit__value__item">
                    <input
                      type="text"
                      :value="generatedSystemvalue(newItem.label)"
                      :class="{ error: valueExistsError }"
                      readonly
                      disabled
                    />
                  </div>

                  <div class="settings__dropdown__edit__value__actions">
                    <span
                      class="settings__dropdown__edit__value__actions__add"
                      @click="addItem()"
                      :class="{ disabled: !rowIsDirty }"
                    >
                      <i class="fa-solid fa-plus"></i>
                    </span>
                  </div>
                </div>
              </li>
            </ul>
            <div class="settings__dropdown__edit__actions">
              <button
                type="button"
                class="settings__dropdown__edit__actions__reset btn"
                :disabled="!listIsDirty"
                @click="resetList()"
              >
                {{ $t("settings.reset") }}
              </button>

              <button
                type="submit"
                class="settings__dropdown__edit__actions__save btn"
                :disabled="!listIsDirty"
                @click="saveList()"
              >
                {{ $t("settings.save") }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="dragIndex !== null"
      class="status-row-drag-ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
        width: ghostWidth || 'auto',
        height: ghostHeight || 'auto',
      }"
    >
      <i class="fa-solid fa-grip-vertical status-row-drag-ghost__handle"></i>
      <StatusBadgePreview
        :label="form.values[dragIndex]?.label"
        :color="form.values[dragIndex]?.color"
        :bg-color="form.values[dragIndex]?.bgColor"
        :icon="form.values[dragIndex]?.icon"
      />
    </div>
  </div>
</template>
