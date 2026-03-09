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
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";
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
  return validationErrors.value.some((item) => item.field === field.name);
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
  } else {
    clearAllValidartionErrors();

    info(t("modules.actions.saving"));
    const moduleSlug = props.module.slug ?? props.module;
    const url = `/${moduleSlug}`;
    validateRequiredFields();
    if (validationErrors.value.length > 0) {
      return;
    }

    form
      .transform((data) => {
        return { ...data };
      })
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

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});

// useUnsavedChangesGuard({
//   getIsDirty: () => form.isDirty,
// });

const module_color = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color;
});
const getField = (f) => {
  return props.fields.find((field) => field.name === f.name);
};
</script>

<template>
  <Head>
    <title>{{ module.label }}</title>
  </Head>

  <div class="record-layout" :style="{ '--module-color': module_color }">
    <div class="record-layout__header">
      <div class="record-layout__header__details">
        <div class="record-layout__header__details__info">
          <div class="record-layout__header__details__info__avatar">
            <i class="fa-solid fa-plus"></i>
          </div>
          <div class="record-layout__header__details__info__text">
            <div class="record-layout__header__details__info__text__name">
              <!-- {{ $t("modules.actions.create_new") }} -->
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
          v-for="s in recordLayout.sections"
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
                :module-color="module_color"
                :has-error="hasError(f)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
