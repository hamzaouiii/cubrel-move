<script setup>
import {
  ref,
  computed,
  getCurrentInstance,
  onMounted,
  onBeforeUnmount,
} from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import { useDragReorder } from "@/Composables/useDragReorder";
import StatusOptionRowFields from "@/Pages/Components/Settings/Dropdowns/StatusOptionRowFields.vue";
import StatusBadgePreview from "@/Pages/Components/Settings/Dropdowns/StatusBadgePreview.vue";

const { error, info, success, clearAllAlerts } = useAlerts();

const generatedSystemKey = computed(() => {
  if (!form.key) return "";
  const name = form.key
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return name + "_list";
});

const props = defineProps({
  isDraft: {
    type: Boolean,
    default: false,
  },
  isStatus: {
    type: Boolean,
    default: false,
  },
  // Used to pre-fill the list name as "{moduleSlug}_{adjustedLabel}", e.g.
  // "deals_status", so the generated system key comes out to
  // "deals_status_list" — matching the convention stock lists already use.
  moduleSlug: {
    type: String,
    default: "",
  },
  fieldLabel: {
    type: String,
    default: "",
  },
});

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
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const appSettings = usePage().props.appSettings;

// A caller may pass a raw translation key (e.g. "modules.deals.fields.status")
// instead of the resolved label — fall back to its last segment so the
// prefill still comes out clean ("status") instead of the whole dotted key.
const cleanFieldLabel = props.fieldLabel.includes(".")
  ? props.fieldLabel.split(".").pop()
  : props.fieldLabel;

// The label may still be empty if the list is created before the field's
// label has been typed (e.g. "Type" is picked before "Label" is filled in
// on the create-field form) — prefill with just the module slug so there's
// still a head start instead of leaving the field completely blank.
const prefilledKey = props.moduleSlug
  ? cleanFieldLabel
    ? `${props.moduleSlug}_${generatedSystemvalue(cleanFieldLabel)}`
    : `${props.moduleSlug}_`
  : "";

const form = useForm({
  key: prefilledKey,
  values: {},
  is_draft: props.isDraft,
  is_status: props.isStatus,
});
let listItems = ref([]);
let newItem = ref({
  label: "",
  value: "",
  color: "#374151",
  bgColor: "#e5e7eb",
  icon: "",
});

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

// Status rows collapse to label/value + pen/bin once added, so building a
// long list doesn't turn into a wall of color pickers; a freshly-added
// blank row opens expanded since there's nothing to collapse yet.
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

const rowIsDirty = computed(() => {
  return newItem.value.label.length >= 3;
});

const valueExistsError = ref(false);

const addItem = () => {
  if (!rowIsDirty.value) {
    return;
  }
  if (
    listItems.value.some(
      (item) => item.value === generatedSystemvalue(newItem.value.label),
    )
  ) {
    error(t("dropdowns.value_already_exists"));
    valueExistsError.value = true;
    return;
  }
  const item = {
    label: newItem.value.label,
    value: generatedSystemvalue(newItem.value.label),
  };
  if (props.isStatus) {
    item.color = newItem.value.color;
    item.bgColor = newItem.value.bgColor;
    item.icon = newItem.value.icon;
  }
  listItems.value.push(item);

  newItem.value = {
    label: "",
    value: "",
    color: "#374151",
    bgColor: "#e5e7eb",
    icon: "",
  };
  valueExistsError.value = false;
};
const deleteItem = (index) => {
  const items = [...listItems.value];
  items.splice(index, 1);
  listItems.value = items;
};

// Status rows are edited in place — "+" just appends a blank, already-live
// row instead of staging one behind a separate commit step.
const addBlankRow = () => {
  listItems.value.push({ label: "", value: "" });
  // Re-read the pushed row through the reactive array instead of using the
  // local object literal — Vue wraps array elements in a reactive proxy on
  // access, so keying off the raw pre-push object would never match what
  // the template's v-for (and thus isExpanded/rowKey) actually sees.
  const pushed = listItems.value[listItems.value.length - 1];
  expandedRows.value = new Set([...expandedRows.value, rowKey(pushed)]);
};

const onLabelInput = (item) => {
  item.value = generatedSystemvalue(item.label);
};

const isDuplicateValue = (item, index) => {
  if (!item.value) return false;
  return listItems.value.some(
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
  if (!props.isStatus) return;
  startDrag(index, event);
};

const onZoneDragOver = (zoneIndex, event) => {
  if (!props.isStatus) return;
  setZoneOver(zoneIndex, event);
};

const onZoneDrop = (zoneIndex) => {
  if (!props.isStatus || dragIndex.value === null) return;
  listItems.value = dropAtZone(listItems.value, zoneIndex);
  endDrag();
};

const onDragEnd = () => {
  endDrag();
};

const listIsDirty = computed(() => {
  return (
    (listItems.value.length || (!props.isStatus && rowIsDirty.value)) &&
    form.key.length
  );
});

const saveList = async () => {
  try {
    if (!props.isStatus && rowIsDirty.value) {
      addItem();
    }
    if (!listItems.value.length) return;

    form.values = listItems.value;
    form.key = generatedSystemKey.value;

    info(t("modules.actions.saving"));
    const response = await axios.post("/settings/dropdowns_in_fields", form);
    clearAllAlerts();
    closeModalClicked();
    success(t("settings.dropdown.save_success"));
    emit("listCreated", response.data);
  } catch (e) {
    clearAllAlerts();
    error(t("settings.dropdown.save_error"));
    console.error(e);
  }
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    if (listIsDirty.value) {
      saveList();
    }
  }
  if (e.key === "Escape" && !listIsDirty.value) {
    e.preventDefault();
    closeModalClicked();
  }
}

onMounted(() => {
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => listIsDirty.value,
});
const emit = defineEmits(["onCloseModal", "listCreated"]);

const closeModalClicked = () => {
  emit("onCloseModal");
};
</script>

<template>
  <div class="dropdown-list-modal" @dragover="onGlobalDragOver">
    <div class="dropdown-list-modal__container">
      <div
        class="settings"
        :style="{
          '--primary-color': appSettings.primary_color,
          '--danger-color': appSettings.danger_color,
        }"
      >
        <div class="settings__dropdown">
          <div class="settings__dropdown__edit">
            <form class="dropdown-form" action="" method="post">
              <div class="dropdown-form__item">
                <span class="dropdown-form__item__label"
                  ><label for="name">Name</label></span
                >
                <div class="dropdown-form__item__field">
                  <input type="text" v-model="form.key" maxlength="25" />
                </div>
              </div>
              <div class="dropdown-form__item" v-if="generatedSystemKey">
                <span class="dropdown-form__item__label"
                  ><label for="name">System Key</label></span
                >
                <div class="dropdown-form__item__field">
                  <span>{{ generatedSystemKey }}</span>
                </div>
              </div>
            </form>

            <div class="settings__dropdown__edit__header">
              <ul class="settings__dropdown__edit__header__info">
                <li class="settings__dropdown__edit__header__info__indicator">
                  <span>{{ $t("settings.dropdown.display_label") }}</span>
                  <span>{{ $t("settings.dropdown.value") }}</span>

                  <div></div>
                </li>
              </ul>
            </div>
            <ul>
              <li
                v-if="isStatus"
                class="status-drop-zone"
                :class="{
                  'status-drop-zone--active':
                    dragIndex !== null && dragOverZone === 0,
                }"
                @dragover="onZoneDragOver(0, $event)"
                @drop="onZoneDrop(0)"
              ></li>
              <template v-for="(l, index) in listItems" :key="rowKey(l)">
              <li
                class="settings__dropdown__edit__value"
                :class="{
                  'settings__dropdown__edit__value--status': isStatus,
                  'settings__dropdown__edit__value--dragging':
                    dragIndex === index,
                }"
                :draggable="isStatus ? 'true' : 'false'"
                @dragstart="onDragStart(index, $event)"
                @dragend="onDragEnd"
              >
                <div class="settings__dropdown__edit__value__row">
                  <span
                    v-if="isStatus"
                    class="settings__dropdown__edit__value__drag-handle"
                  >
                    <i class="fa-solid fa-grip-vertical"></i>
                  </span>

                  <div
                    v-if="isStatus"
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
                      v-if="isStatus && isExpanded(l)"
                      type="text"
                      v-model="l.label"
                      :placeholder="$t('settings.dropdown.display_label')"
                      @input="onLabelInput(l)"
                    />
                    <StatusBadgePreview
                      v-else-if="isStatus"
                      :label="l.label"
                      :color="l.color"
                      :bg-color="l.bgColor"
                      :icon="l.icon"
                    />
                    <span v-else>{{ $t(l.label) }}</span>
                  </div>

                  <div class="settings__dropdown__edit__value__item">
                    <input
                      v-if="isStatus && isExpanded(l)"
                      type="text"
                      :value="l.value"
                      :class="{ error: isDuplicateValue(l, index) }"
                      readonly
                      disabled
                    />
                    <span v-else>{{ $t(l.value) }}</span>
                  </div>

                  <div
                    v-if="!isStatus"
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
                  v-if="isStatus && isExpanded(l)"
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
                v-if="isStatus"
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
                v-if="isStatus"
                class="settings__dropdown__edit__value settings__dropdown__edit__value--add"
                @click="addBlankRow"
              >
                <i class="fa-solid fa-circle-plus"></i>
                <span>{{ $t("settings.dropdown.add_option") }}</span>
              </li>
              <form v-else class="settings__dropdown__edit__value">
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
              </form>
            </ul>

            <div class="settings__actions">
              <button
                type="submit"
                class="settings__actions__reset"
                @click="closeModalClicked"
              >
                {{ $t("settings.cancel") }}
              </button>
              <button
                type="submit"
                class="settings__actions__save"
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
        :label="listItems[dragIndex]?.label"
        :color="listItems[dragIndex]?.color"
        :bg-color="listItems[dragIndex]?.bgColor"
        :icon="listItems[dragIndex]?.icon"
      />
    </div>
  </div>
</template>
