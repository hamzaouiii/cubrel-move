<script setup>
import Layout from "@/Layouts/Layout.vue";
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
import ModuleDropdownField from "../Components/FiledTypes/ModuleDropdownField.vue";
import DateTime from "../Components/FiledTypes/DateTime.vue";
const { success, error, info, warning, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  recordLayout: Object,
  dropdownLists: Object,
  fields: Object,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const buildInitialForm = () => {
  const data = {};

  if (props.recordLayout && props.recordLayout.sections) {
    props.recordLayout.sections.forEach((section) => {
      section.layout.forEach((field) => {
        data[field.name] = "";
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

const hasError = computed(() => (field) => {
  return validationErrors.value.some((item) => item.field === field.label);
});

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

const getRequiredFields = () => {
  const sections = props.recordLayout?.sections;
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
      field: item.label,
      type: "required",
    });
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

const removeValidationError = (field) => {
  if (hasError.value(field)) {
    validationErrors.value = validationErrors.value.filter(
      (item) => item.field !== field.label,
    );
  }
};

const removeValidationErrorText = (field) => {
  if (form[field.name].length >= 3 && hasError.value(field)) {
    validationErrors.value = validationErrors.value.filter(
      (item) => item.field !== field.label,
    );
  }
};

const saveRecord = () => {
  if (!form.isDirty) {
    warning(t("modules.actions.no_data_entered"));
  } else {
    info(t("modules.actions.saving"));
    clearAllValidartionErrors();
    const moduleSlug = props.module.slug ?? props.module;
    const url = `/${moduleSlug}`;
    validateRequiredFields();
    if (validationErrors.value.length > 0) {
      return;
    }

    form.post(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("modules.actions.save_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.actions.save_error") + form.errors);
      },
    });
  }
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

const getFieldDropDownList = (f) => {
  const field = props.fields.find((field) => field.name === f.name);
  const list = props.dropdownLists.find((l) => l.field_key === field.key);

  return list?.values || [];
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
  getIsDirty: () => form.isDirty,
});

const getTextareaRows = (f) => {
  if (form[f.name]) {
    const val = form[f.name].split(" ").length;
    return val / 8;
  }
  return 2;
};
</script>

<template>
  <Head>
    <title>{{ module.label }} - Automatisierung Regensburg</title>
  </Head>

  <div
    class="module-layout"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
    <div class="module-layout__header">
      <div class="module-layout__header__details">
        <h1 class="module-layout__header__details__title"></h1>
      </div>

      <div class="module-layout__header__actions" ref="actionDropDownref">
        <div class="module-layout__header__actions__create">
          <button
            class="module-layout__header__actions__create__cancel-btn"
            @click="cancelCreate"
          >
            {{ $t("modules.actions.cancel") || "Cancel" }}
          </button>

          <button
            class="module-layout__header__actions__create_save-btn"
            :disabled="!form.isDirty"
            @click="saveRecord"
          >
            {{ $t("modules.actions.save") || "Save" }}
          </button>
        </div>
      </div>
    </div>

    <div class="module-layout__record">
      <div
        class="module-layout__record__section"
        v-for="s in recordLayout.sections"
        :key="s.name"
      >
        <div class="module-layout__record__section__title">
          {{ s.name }}
        </div>

        <div class="module-layout__record__section__layout">
          <div
            v-for="f in s.layout.filter((f) => !f.readonly)"
            :key="f.name"
            class="module-layout__record__section__layout__field"
          >
            <span class="module-layout__record__section__layout__field__label">
              {{ $t(f.label) }}:
            </span>

            <div
              class="module-layout__record__section__layout__field__content editing-mode"
              :class="{ error: hasError(f) }"
            >
              <template v-if="f.type === 'datetime'">
                <DateTime
                  v-model="form[f.name]"
                  type="datetime"
                  @click="removeValidationError(f)"
                ></DateTime>
              </template>
              <template v-else-if="f.type === 'date'">
                <DateTime
                  v-model="form[f.name]"
                  type="date"
                  @click="removeValidationError(f)"
                ></DateTime>
              </template>
              <template v-else-if="f.type === 'longtext'">
                <textarea
                  v-model="form[f.name]"
                  :rows="getTextareaRows(f)"
                ></textarea>
              </template>
              <template v-else-if="f.type === 'dropdown'">
                <ModuleDropdownField
                  :options="getFieldDropDownList(f)"
                  v-model="form[f.name]"
                  @click="removeValidationError(f)"
                ></ModuleDropdownField>
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
  </div>
</template>
