<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import {
  ref,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
  computed,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";

import { useFieldValidation } from "@/Composables/useFieldValidation";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";

defineOptions({
  layout: AppLayout,
});

const props = defineProps({
  module: Object,
  title: String,
  recordLayout: Object,
  dropdownLists: Object,
  fields: Object,
});
const { validateFieldTypes } = useFieldValidation(props);
const { success, error, info, warning, clearAllAlerts } = useAlerts();

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const buildInitialForm = () => {
  const data = {};

  if (props.recordLayout && props.recordLayout.sections) {
    props.recordLayout.sections.forEach((section) => {
      section.layout.forEach((field) => {
        if (!(field.name === "created_at" || field.name === "updated_at")) {
          data[field.name] = "";
        }
      });
    });
  }

  return data;
};

const appSettings = usePage().props.appSettings;
const form = useForm(buildInitialForm());
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);
const validationErrors = ref([]);

const fieldOverlayOpen = ref(false);
const activeField = ref(null);

const hasError = computed(() => (field) => {
  return validationErrors.value.some((item) => item.field === field.name);
});

// No module currently has an avatar field, so the header shows initials
// only, derived live from the name field as it's typed.
const avatar = computed(() => {
  const name = form.name?.trim();
  if (!name) return "+";

  const cleaned = name.replace(/\d+/g, "");
  const words = cleaned.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }

  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
});

const openFieldOverlay = (field) => {
  activeField.value = field;
  fieldOverlayOpen.value = true;
};

const onFieldRecordSelect = (record) => {
  if (!activeField.value) return;
  const fieldName = activeField.value.name;
  form[fieldName] = record.id;
  form[fieldName + "__label"] = record.name;
  fieldOverlayOpen.value = false;
  activeField.value = null;
};
const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
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

const validateRequiredFields = () => {
  requiredEmptyFields.value.map((item) => {
    validationErrors.value.push({
      field: item.name,
      label: item.label,
      type: "required",
    });
  });

  if (validationErrors.value.length > 1) {
    clearAllAlerts();
    error(t("fields.validation.is_required_several"));
  } else if (validationErrors.value.length === 1) {
    clearAllAlerts();
    error(
      t(validationErrors.value[0].label) +
        " " +
        t("fields.validation.is_required"),
    );
  }
};

const clearAllValidartionErrors = () => {
  clearAllAlerts();
  validationErrors.value = [];
};

const saveRecord = () => {
  if (!form.isDirty) {
    warning(t("modules.actions.no_data_entered"));
    return;
  }

  clearAllValidartionErrors();
  info(t("modules.actions.saving"));

  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}`;

  validateRequiredFields();
  if (validationErrors.value.length > 0) return;

  const typeErrors = validateFieldTypes(form.data());
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

  form
    .transform((data) => ({ ...data }))
    .post(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("modules.actions.save_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.actions.save_error") + form.errors);
      },
    });
};

const cancelCreate = () => {
  clearAllValidartionErrors();
  const moduleSlug = props.module.slug ?? props.module;
  router.visit(`/${moduleSlug}`);
};

function handleKeydown(e) {
  if (e.ctrlKey && e.key === "s") {
    e.preventDefault();
    saveRecord();
  }

  if (e.key === "Escape") {
    e.preventDefault();
    cancelCreate();
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});

useUnsavedChangesGuard({
  getIsDirty: () => form.isDirty,
});

const module_color = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color;
});
const getField = (f) => {
  return props.fields.find((field) => field.name === f.name);
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
</script>

<template>
  <Head>
    <title>{{ module.label }} - Cubrel</title>
  </Head>

  <div class="record-layout" :style="{ '--module-color': module_color }">
    <div class="record-layout__header">
      <div class="record-layout__header__details">
        <div class="record-layout__header__details__info">
          <div
            class="record-layout__header__details__info__avatar-text"
            v-if="avatar"
          >
            {{ avatar }}
          </div>

          <div class="record-layout__header__details__info__text">
            <div class="record-layout__header__details__info__text__name">
              {{ form.name ?? "" }}
            </div>
            <div
              class="record-layout__header__details__info__text__description"
            >
              <!-- {{ $t("modules.fill_details") }} -->
            </div>
          </div>
        </div>

        <div class="record-layout__header__details__actions">
          <div class="record-layout__header__details__actions__edit">
            <button @click="cancelCreate">
              {{ $t("modules.actions.cancel") }}
            </button>
            <button :disabled="!form.isDirty" @click="saveRecord">
              {{ $t("modules.actions.save") }}
            </button>
          </div>
        </div>
      </div>

      <div class="record-layout__header__tabs">
        <ul>
          <li class="active">{{ $t("modules.overview") }}</li>
        </ul>
      </div>
    </div>
    <div class="record-layout__scroll">
      <div class="record-layout__sections">
        <div
          class="record-layout__sections__item"
          v-for="s in recordLayout.sections.filter((s) => !s.has_line_items)"
          :key="s.name"
        >
          <div class="record-layout__sections__item__title">
            {{ s.name }}
          </div>

          <div class="record-layout__sections__item__layout">
            <div
              v-for="f in s.layout.filter((f) => !getField(f).readonly)"
              :key="f.name"
              class="record-layout__sections__item__layout__field"
            >
              <span class="record-layout__sections__item__layout__field__label">
                {{ $t(f.label) }}:
              </span>
              <FieldRenderer
                :field="getField(f)"
                v-model="form[f.name]"
                mode="edit"
                :related_label="form[f.name + '__label'] ?? null"
                :module-color="module_color"
                :icon="getIcon(getField(f).related_module)"
                :has-error="hasError(f)"
                @open-link-overlay="openFieldOverlay"
              />
            </div>
          </div>
        </div>
      </div>
      <RecordSelectorDrawer
        :open="fieldOverlayOpen"
        :search-endpoint="
          activeField
            ? `/relatedfield/search/${activeField.related_module}`
            : ''
        "
        :related-module="activeField?.related_module"
        :icon="getIcon(activeField?.related_module || null)"
        :layout="getLinkingLayout(activeField?.related_module || null)"
        @select="onFieldRecordSelect"
        @close="
          fieldOverlayOpen = false;
          activeField = null;
        "
        :selected-user="form[activeField?.name]"
        :active-field="activeField"
        :fields="fields"
      />
    </div>
  </div>
</template>
