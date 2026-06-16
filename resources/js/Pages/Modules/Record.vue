<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
  toRaw,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";
import PanelList from "@/Pages/Components/Modules/Relatedpanels/PanelList.vue";
import RelatedLinksOverlay from "@/Pages/Components/Modules/RelatedLinksOverlay.vue";
import { useFieldValidation } from "@/Composables/useFieldValidation";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";
import LineItemsPanel from "../Components/Modules/LineItemsPanel.vue";
import PdfModal from "@/Pages/Components/Modules/PdfModal.vue";
import ExportModal from "@/Pages/Components/Modules/ExportModal.vue";

const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();

defineOptions({
  layout: AppLayout,
});
const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  overviewLayout: Object,
  relatedLayout: Object,
  fields: Object,
  lineItemFields: Array,
  productFields: Array,
  pdfTemplates: { type: Array, default: () => [] },
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;
const { validateFieldTypes } = useFieldValidation(props);
// State

const form = useForm({ ...props.record });

const isEditing = ref(false);
const validationErrors = ref([]);
const showActionDropDown = ref(false);
const showPdfModal = ref(false);
const showExportModal = ref(false);
const actionDropDownref = ref(null);
const currentTab = ref("overview");
const overlayOpen = ref(false);
const fieldOverlayOpen = ref(false);
const activePanel = ref(null);
const activeParentRecord = ref(null);
const expandPanel = ref(null);
const activeField = ref(null);
// Computed
const avatar = computed(() => {
  const name = props.record?.name?.trim();
  if (!name) return "";

  const cleaned = name.replace(/\d+/g, "");
  const words = cleaned.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }

  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
});

const mode = computed(() => {
  return isEditing.value === true ? "edit" : "detail";
});

const isDirty = computed(() => form.isDirty);

const hasError = computed(() => (field) => {
  return validationErrors.value.some((item) => item.field === field.name);
});

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
const module_color = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color;
});

// Methods

/// Adjsut this to use props.fields instead of layout as source of truth for type.
const getFieldType = (fieldName) => {
  return props.fields?.find((field) => field.name === fieldName)?.type || null;
};

const getRequiredFields = () => {
  let allRequiredFields = [];

  const requiredFields = props.fields?.filter(
    (field) => field.required === true && field.readonly !== true,
  );
  if (requiredFields?.length) {
    allRequiredFields.push(...requiredFields);
  }

  return allRequiredFields;
};

const getRequiredFieldsFromPayload = (payload) => {
  return getRequiredFields().filter((item) =>
    payload.hasOwnProperty(item.name),
  );
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

const normalizeDateTime = (value) => {
  const d = new Date(value);
  return d.toISOString().slice(0, 19).replace("T", " ");
};

const validateRequiredFields = (payload) => {
  const requiredErrors = [];

  requiredEmptyFields.value.forEach((item) => {
    requiredErrors.push({
      field: item.name,
      label: item.label,
      type: "required",
    });
  });

  const fields = getRequiredFieldsFromPayload(payload);
  fields.forEach((item) => {
    if (!payload[item.name]) {
      requiredErrors.push({
        field: item.name,
        label: item.label,
        type: "required",
      });
    }
  });

  const unique = Array.from(
    new Map(requiredErrors.map((e) => [`${e.field}-${e.type}`, e])).values(),
  );

  validationErrors.value = unique;
  if (unique.length > 1) {
    clearAllAlerts();
    error(t("fields.validation.is_required_several"));
  } else if (unique.length === 1) {
    clearAllAlerts();
    error(t(unique[0].label) + " " + t("fields.validation.is_required"));
  }
};

const clearAllValidationErrors = () => {
  clearAllAlerts();
  validationErrors.value = [];
};

const saveRecord = () => {
  clearAllValidationErrors();
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

  const typeErrors = validateFieldTypes(payload);

  if (typeErrors.length > 0) {
    validationErrors.value = [...validationErrors.value, ...typeErrors];

    clearAllAlerts();

    if (typeErrors.length > 1) {
      error(t("fields.validation.invalid_several"));
    } else {
      error(
        t(typeErrors[0].label) + " " + t("fields.validation.invalid_format"),
      );
    }

    return;
  }

  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}/${props.record.id}`;

  form
    .transform((data) => {
      return { ...data };
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

const openPdf = () => {
  showActionDropDown.value = false;
  showPdfModal.value = true;
};

const openExport = () => {
  showActionDropDown.value = false;
  showExportModal.value = true;
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

const enableEditing = () => {
  isEditing.value = true;
};

const cancelEditing = () => {
  form.reset();
  isEditing.value = false;
  clearAllValidationErrors();
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

const handleKeydown = (e) => {
  if (overlayOpen.value || fieldOverlayOpen.value) return;
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
};

const getField = (f) => {
  const pp = props.fields.find((field) => field.name === f.name);
  return pp;
};

const getMode = (f) => {
  if (f.readonly) return "detail";
  return mode.value;
};

const switchTabs = (tab) => {
  currentTab.value = tab;
};

const openOverlay = (panel, selected) => {
  activePanel.value = panel;
  overlayOpen.value = true;
  activeParentRecord.value = selected;
};

const openFieldOverlay = (field) => {
  activeField.value = field;
  fieldOverlayOpen.value = true;
};

const activeLayout = (panel) => {
  return props.record?.related[panel?.name]?.linkingPanel.columns || null;
};

const handleSaved = (p) => {
  expandPanel.value = p;
  overlayOpen.value = false;
  router.reload({
    only: ["record"],
    preserveScroll: true,
    preserveState: true,
  });
};

const showRelatedTab = computed(() => {
  return (
    !isEditing.value &&
    Object.keys(props.record.related).length > 0 &&
    props.record?.related.constructor === Object
  );
});
// Lifecycle
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

const relationship = (name) => {
  return props.record?.related?.[name] || null;
};

const onFieldRecordSelect = (record) => {
  if (!activeField.value) return;

  const fieldName = activeField.value.name;

  form[fieldName] = record.id;

  form[fieldName + "__label"] = record.name;

  fieldOverlayOpen.value = false;
  activeField.value = null;
};
const allModules = computed(() => usePage().props.modules);
const allLayouts = computed(() => usePage().props.layouts);
const getIcon = (slug) => {
  if (!slug) {
    return;
  }
  const m = allModules.value.find((m) => m.slug === slug);

  return m?.icon || "fa-solid fa-user";
};

const getLinkingLayout = (slug) => {
  if (!slug) {
    return;
  }

  const l = allLayouts.value.find((l) => l.module === slug);
  return l?.layouts?.linkingPanel?.columns || null;
};

const handleTotalsUpdated = (totals) => {
  form.subtotal = totals.subtotal;
  form.tax_amount = totals.tax_amount;
  form.discount_amount = totals.discount_amount;
  form.total = totals.total;

  form.defaults({
    subtotal: totals.subtotal,
    tax_amount: totals.tax_amount,
    discount_amount: totals.discount_amount,
    total: totals.total,
  });

  const moduleSlug = props.module.slug ?? props.module;
  router.put(`/${moduleSlug}/${props.record.id}`, totals, {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>
<template>
  <Head>
    <title>{{ record.name }} - {{ title }} - Cubrel</title>
  </Head>
  <div class="record-layout" :style="{ '--module-color': module_color }">
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
                  @click="openExport()"
                  class="record-layout__header__details__actions__edit__dropdown__item"
                >
                  <i class="fa-solid fa-download"></i>
                  <span>{{ $t("modules.actions.export") }}</span>
                </li>
                <li
                  v-if="pdfTemplates.length > 0"
                  @click="openPdf()"
                  class="record-layout__header__details__actions__edit__dropdown__item"
                >
                  <i class="fa-solid fa-file-pdf"></i>
                  <span>{{ $t("modules.actions.pdf") }}</span>
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
            v-if="showRelatedTab"
            @click="switchTabs('related')"
            :class="{ active: currentTab === 'related' }"
          >
            {{ $t("modules.related") }}
          </li>
        </ul>
      </div>
    </div>
    <div class="record-layout__scroll">
      <div v-show="currentTab !== 'related'" class="record-layout__sections">
        <div
          class="record-layout__sections__item"
          v-for="s in overviewLayout.sections"
        >
          <!-- In Record.vue, inside the sections loop -->
          <template v-if="s.has_line_items">
            <div class="record-layout__sections__item__title">
              {{ s.name }}
            </div>
            <LineItemsPanel
              :parent-id="record.id"
              :parent-type="module.slug"
              :mode="mode"
              :module-color="module_color"
              :product-fields="productFields"
              :line-item-fields="lineItemFields"
              @totals-updated="handleTotalsUpdated"
            />
          </template>
          <template v-else>
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
                <FieldRenderer
                  :field="getField(f)"
                  v-model="form[f.name]"
                  :related_label="form[f.name + '__label'] ?? null"
                  :mode="getMode(f)"
                  :read-only="getField(f)?.readonly"
                  :module-color="module_color"
                  :icon="getIcon(getField(f)?.related_module || null)"
                  :has-error="hasError(f)"
                  @open-link-overlay="openFieldOverlay"
                />
              </div>
            </div>
          </template>
        </div>
      </div>

      <div v-show="currentTab === 'related'" class="record-layout__subpanels">
        <PanelList
          :relationships="record.related"
          :layout="relatedLayout"
          @open-overlay="openOverlay"
          @panel-update="handleSaved"
          :expand-panel="expandPanel"
        ></PanelList>
        <RelatedLinksOverlay
          v-if="overlayOpen"
          :layout="activeLayout(activePanel)"
          :panel="activePanel"
          :relationship="relationship(activePanel.name)"
          @close="overlayOpen = false"
          @saved="handleSaved"
          :selected-parent="activeParentRecord"
        />
      </div>
      <PdfModal
        v-if="showPdfModal"
        :module-slug="module.slug"
        :record-id="record.id"
        :record-name="record.name ?? record.number ?? record.id"
        :templates="pdfTemplates"
        @close="showPdfModal = false"
      />

      <ExportModal
        v-if="showExportModal"
        :module-slug="module.slug"
        :record-id="record.id"
        :record-name="record.name ?? record.number ?? record.id"
        @close="showExportModal = false"
      />

      <RecordSelectorDrawer
        :open="fieldOverlayOpen"
        :search-endpoint="
          activeField
            ? `/relatedfield/search/${activeField.related_module}`
            : ''
        "
        :related-module="activeField?.related_module"
        :icon="getIcon(activeField?.related_module || null)"
        @select="onFieldRecordSelect"
        @close="
          fieldOverlayOpen = false;
          activeField = null;
        "
        :selected-record="form[activeField?.name]"
        :active-field="activeField"
        :layout="getLinkingLayout(activeField?.related_module || null)"
        :fields="fields"
      />
    </div>
  </div>
</template>
