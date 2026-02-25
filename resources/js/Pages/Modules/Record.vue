<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
} from "vue";
import { formatDateTime, formatDate } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

import ModuleDropdownField from "@/Pages/Components/FiledTypes/ModuleDropdownField.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import PanelList from "@/Pages/Components/Modules/Relatedpanels/PanelList.vue";
import RelatedLinksOverlay from "@/Pages/Components/Modules/RelatedLinksOverlay.vue";
const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  overviewLayout: Object,
  relatedLayout: Object,
  fields: Object,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const avatar = computed(() => {
  const name = props.record?.name?.trim();
  if (!name) return "";

  // Remove numbers
  const cleaned = name.replace(/\d+/g, "");

  const words = cleaned.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }

  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
});
// function debugStructure(obj) {
//   const raw = JSON.parse(JSON.stringify(obj));
//   console.log(JSON.stringify(raw, null, 2));
// }
// debugStructure(props.relatedLayout);
const isRelatedLayoutEmpty = computed(() => {
  return props.relatedLayout?.columns?.every(
    (column) => Array.isArray(column) && column.length === 0,
  );
});

const form = useForm({ ...props.record });
const isEditing = ref(false);
const validationErrors = ref([]);
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);
const appSettings = usePage().props.appSettings;

const currentTab = ref("related");

const isDirty = computed(() => form.isDirty);

const hasError = computed(() => (field) => {
  return validationErrors.value.some((item) => item.field === field.label);
});

const getFieldType = (field) => {
  const sections = props.overviewLayout?.sections;
  for (const section of sections) {
    const found = section.layout?.find((item) => item.name === field);
    if (found) {
      return found.type;
    }
  }
  return "no_type";
};

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value;
};

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

const enableEditing = () => {
  isEditing.value = true;
};

const getChangedData = (original, form) => {
  const changed = {};
  const edited = form.data();
  for (const key of Object.keys(edited)) {
    if (original[key] !== edited[key]) {
      if (getFieldType(key) === "datetime" || getFieldType(key) === "date") {
        changed[key] = normalizeDateTime(edited[key]);
      } else {
        changed[key] = edited[key];
      }
    }
  }

  return changed;
};

function normalizeDateTime(value) {
  const d = new Date(value);
  return d.toISOString().slice(0, 19).replace("T", " ");
}

const getRequiredFields = () => {
  const sections = props.overviewLayout?.sections;
  let allRequiredFields = [];

  for (const section of sections) {
    const requiredFields = section.layout?.filter(
      (item) => item.required === true && item.readonly !== true,
    );
    if (requiredFields?.length) {
      allRequiredFields.push(...requiredFields);
    }
  }
  return allRequiredFields;
};

const getRequiredFieldsFromPayload = (payload) => {
  return getRequiredFields().filter((item) =>
    payload.hasOwnProperty(item.name),
  );
};

const emptyFields = computed(() => {
  return Object.entries(form)
    .filter(([key, value]) => {
      return (
        value === "" ||
        value === "---" ||
        value === null ||
        value === undefined ||
        (Array.isArray(value) && value.length === 0)
      );
    })
    .map(([key]) => key);
});

const requiredEmptyFields = computed(() => {
  const requiredFields = getRequiredFields();

  return requiredFields.filter((field) =>
    emptyFields.value.includes(field.name),
  );
});

// This ended up being way too complex due to several data manupulations for different data types, I am sure there is a better way to achieve this
const validateRequiredFields = (payload) => {
  // upon saving if a field is required and empty then immediately add to validationErrors[] - no need to check anything else. This only validates empty fields that should not be empty. Meaning a record cannot be edited or saved without having to solve this issue. In reality this should never happen since same validation happens upon creating new records.
  requiredEmptyFields.value.map((item) => {
    validationErrors.value.push({
      field: item.label,
      type: "required",
    });
  });

  // Now we need to check the payload, if a field had a value before and is now empty, we need to stop the saving proccess by adding to validationErrors[]
  const fields = getRequiredFieldsFromPayload(payload);
  fields.map((item) => {
    if (!payload[item.name]) {
      validationErrors.value.push({
        field: item.label,
        type: "required",
      });
    }
  });
  if (validationErrors.value.length > 1) {
    clearAllAlerts();
    error(t("fields.validation.is_required_several"));
  } else if (validationErrors.value.length === 1) {
    clearAllAlerts();
    error(
      t(validationErrors.value[0].field) +
        " " +
        t("fields.validation.is_required"),
    );
  }
};

const clearAllValidartionErrors = () => {
  clearAllAlerts();
  validationErrors.value = [];
};

const removeValidationErrorText = (field) => {
  if (form[field]?.length >= 3 && hasError.value(field)) {
    validationErrors.value = validationErrors.value.filter(
      (item) => item.field !== field.label,
    );
  }
};

const removeValidationError = (field) => {
  if (hasError.value(field)) {
    validationErrors.value = validationErrors.value.filter(
      (item) => item.field !== field.label,
    );
  }
};

const saveRecord = () => {
  clearAllValidartionErrors();

  info(t("modules.actions.updating"));

  const payload = getChangedData(props.record, form);
  if (Object.keys(payload).length === 0) {
    isEditing.value = false;
    return;
  }
  validateRequiredFields(payload);
  if (validationErrors.value.length > 0) {
    return;
  }
  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}/${props.record.id}`;
  form
    .transform((data) => {
      const payload = { ...data };
      return payload;
    })
    .put(url, {
      onSuccess: () => {
        isEditing.value = false;
        clearAllAlerts();
        success(t("modules.actions.update_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.actions.update_error") + form.errors);
      },
    });
};

const deleteRecord = async () => {
  const ok = await confirm({
    title: t("modules.actions.delete_title"),
    message: t("modules.actions.delete_confirm"),
    confirmText: t("modules.actions.delete_yes"),
    cancelText: t("modules.actions.delete_no"),
    danger: true,
  });

  if (!ok) return;
  info(t("modules.actions.deleting"));

  form.delete(`/${props.module.slug}/${props.record.id}`, {
    onSuccess: () => {
      clearAllAlerts();
      success(t("modules.actions.delete_success"));
    },
    onError: () => {
      clearAllAlerts();
      const serverError = JSON.stringify(form.errors);
      error(t("modules.actions.delete_error") + serverError);
    },
  });
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    if (isEditing.value) {
      saveRecord();
    }
  }

  if (e.ctrlKey && e.key === "e") {
    e.preventDefault();
    enableEditing();
  }

  if (e.key === "Escape") {
    cancelEditing();
  }
}

const cancelEditing = () => {
  form.reset();
  isEditing.value = false;
  clearAllValidartionErrors();
};

const displayValueFor = (f) => {
  const val = props.record[f.name];
  if (val == null || val === "") return "";

  if (f.type === "datetime") {
    return formatDateTime(val, appSettings);
  }
  if (f.type === "date") {
    return formatDate(val, appSettings);
  }

  if (f.type === "longtext") {
    if (val.length > 62) {
      return val.substring(0, 64) + "...";
    }
  }
  if (f.type === "dropdown") {
    return getDropDownListLabel(f);
  }
  return val;
};

const getTextareaRows = (f) => {
  if (form[f.name]) {
    const val = form[f.name].split(" ").length;
    return val / 8;
  }
  return 3;
};

const isDropDown = (f) => {
  return f.type === "dropdown";
};

const getFieldDropDownList = (f) => {
  const field = props.fields.find((field) => field.name === f.name);
  return field?.dropdown_list.values || [];
};

const getDropDownListLabel = (f) => {
  const list = getFieldDropDownList(f);
  const label = list.find((l) => l.value === form[f.name])?.label || "---";
  return t(label);
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});
useUnsavedChangesGuard({
  getIsDirty: () => isDirty.value,
});

const switchTabs = (tab) => {
  currentTab.value = tab;
};

// for related overlay
const overlayOpen = ref(false);
const activePanel = ref(null);
const activeParentRecord = ref(null);

const openOverlay = (panel, selected) => {
  activePanel.value = panel;
  overlayOpen.value = true;
  activeParentRecord.value = selected;
};

const activeLayout = (panel) =>
  props.record?.related[panel?.name]?.linking_layout.columns || null;

const handleSaved = () => {
  overlayOpen.value = false;

  router.reload({
    only: ["record"],
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<template>
  <Head>
    <title>{{ record.name }} - {{ title }}</title>
  </Head>

  <div
    class="module-layout"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="record-layout">
      <div class="record-layout__scroll">
        <div class="record-layout__header">
          <div class="record-layout__header__details">
            <div class="record-layout__header__details__info">
              <div
                class="record-layout__header__details__info__avatar"
                v-if="record.avatar"
              >
                {{ record.avatar }}
              </div>
              <div
                class="record-layout__header__details__info__avatar"
                v-else="record.avatar"
              >
                {{ avatar }}
              </div>
              <div class="record-layout__header__details__info__text">
                <div class="record-layout__header__details__info__text__name">
                  {{ record.name }}
                </div>
                <div
                  class="record-layout__header__details__info__text__description"
                >
                  {{ record.description }}
                </div>
              </div>
            </div>
            <div
              class="record-layout__header__details__actions"
              ref="actionDropDownref"
            >
              <div class="record-layout__header__details__actions__edit">
                <button v-if="isEditing" @click="cancelEditing">
                  {{ $t("modules.actions.cancel") }}
                </button>

                <button v-if="!isEditing" @click="enableEditing">
                  {{ $t("modules.actions.edit") }}
                </button>

                <button v-else :disabled="!isDirty" @click="saveRecord">
                  {{ $t("modules.actions.save") }}
                </button>

                <button
                  @click="toggleActionDropDown"
                  class="record-layout__header__details__actions__edit__dropdown-btn"
                >
                  <i
                    :class="
                      showActionDropDown
                        ? 'fa-solid fa-chevron-up'
                        : 'fa-solid fa-chevron-down'
                    "
                  ></i>
                </button>

                <transition name="fade">
                  <ul
                    v-if="showActionDropDown"
                    class="record-layout__header__details__actions__edit__dropdown show"
                  >
                    <li
                      class="record-layout__header__details__actions__edit__dropdown__item disabled"
                    >
                      <i class="fa-solid fa-share-from-square"></i>
                      <span>{{ $t("modules.actions.share") }}</span>
                    </li>
                    <li
                      class="record-layout__header__details__actions__edit__dropdown__item disabled"
                    >
                      <i class="fa-solid fa-download"></i>
                      <span>{{ $t("modules.actions.export") }}</span>
                    </li>
                    <li
                      class="record-layout__header__details__actions__edit__dropdown__item"
                    >
                      <i class="fa-solid fa-hourglass-end"></i>
                      <span>{{ $t("modules.actions.placeholder") }}</span>
                    </li>
                    <li
                      class="record-layout__header__details__actions__edit__dropdown__item"
                    >
                      <i class="fa-solid fa-file-pdf"></i>
                      <span>{{ $t("modules.actions.bulk_action") }}</span>
                    </li>
                    <li
                      @click="deleteRecord()"
                      class="record-layout__header__details__actions__edit__dropdown__item"
                      style="color: salmon"
                    >
                      <i class="fa-solid fa-trash-can"></i>
                      <span>{{ $t("modules.actions.delete") }}</span>
                    </li>
                  </ul>
                </transition>
              </div>
            </div>
          </div>

          <div class="record-layout__header__tabs">
            <ul>
              <li
                @click="switchTabs('overview')"
                :class="{ active: currentTab === 'overview' }"
              >
                {{ $t("modules.overview") }}
              </li>
              <li
                @click="switchTabs('related')"
                :class="{ active: currentTab === 'related' }"
              >
                {{ $t("modules.related") }}
              </li>
            </ul>
          </div>
        </div>
        <div v-if="currentTab === 'overview'" class="record-layout__sections">
          <div
            class="record-layout__sections__item"
            v-for="s in overviewLayout.sections"
          >
            <div class="record-layout__sections__item__title">
              {{ s.name }}
            </div>
            <div class="record-layout__sections__item__layout">
              <div
                v-for="f in s.layout"
                class="record-layout__sections__item__layout__field"
              >
                <span
                  class="record-layout__sections__item__layout__field__label"
                >
                  {{ $t(f.label) }}:
                </span>

                <div
                  v-if="!isEditing"
                  :class="[
                    'record-layout__sections__item__layout__field__content',
                    { 'view-uneditable-field': f.readonly },
                  ]"
                  @click="!f.readonly && enableEditing()"
                >
                  {{ displayValueFor(f) }}
                </div>
                <div
                  :class="[
                    'record-layout__sections__item__layout__field__content',
                    'editing-mode',
                    { 'uneditable-field': f.readonly },
                    { error: hasError(f) },
                  ]"
                  v-else
                >
                  <template v-if="f.readonly">
                    <span>
                      {{ displayValueFor(f) }}
                    </span>
                  </template>
                  <template v-else-if="isDropDown(f)">
                    <ModuleDropdownField
                      :options="getFieldDropDownList(f)"
                      v-model="form[f.name]"
                      @click="removeValidationError(f)"
                    ></ModuleDropdownField>
                  </template>
                  <template v-else-if="f.type == 'longtext'">
                    <textarea
                      v-model="form[f.name]"
                      :rows="getTextareaRows(f)"
                      @input="removeValidationErrorText(f)"
                    ></textarea>
                    <span v-if="hasError(f)" class="error-icon-container">
                      <i class="error-icon fa-solid fa-circle-exclamation"></i>
                    </span>
                  </template>
                  <template v-else-if="f.type == 'datetime'">
                    <DateTime
                      v-model="form[f.name]"
                      type="datetime"
                      @click="removeValidationError(f)"
                    />
                  </template>
                  <template v-else-if="f.type == 'date'">
                    <DateTime
                      v-model="form[f.name]"
                      type="date"
                      @click="removeValidationError(f)"
                    />
                  </template>
                  <template v-else>
                    <input
                      type="text"
                      v-model="form[f.name]"
                      @input="removeValidationErrorText(f)"
                    />
                  </template>
                  <span v-if="hasError(f)" class="error-icon-container">
                    <i class="error-icon fa-solid fa-circle-exclamation"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          v-else-if="currentTab === 'related'"
          class="record-layout__subpanels"
        >
          <PanelList
            :relationships="record.related"
            :layout="relatedLayout"
            @open-overlay="openOverlay"
          ></PanelList>
          <RelatedLinksOverlay
            v-if="overlayOpen"
            :layout="activeLayout(activePanel)"
            :panel="activePanel"
            @close="overlayOpen = false"
            @saved="handleSaved"
            :selected-parent="activeParentRecord"
          />
        </div>
      </div>
    </div>
  </div>
</template>
