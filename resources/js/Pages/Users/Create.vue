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
import { useConfirm } from "@/Composables/useConfirm";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";

import { useFieldValidation } from "@/Composables/useFieldValidation";

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
const { confirm } = useConfirm();

const { proxy } = getCurrentInstance();
const t = proxy.$t;

console.log(props.recordLayout);

const buildInitialForm = () => {
  const data = {};
  if (props.recordLayout && props.recordLayout.sections) {
    props.recordLayout.sections.forEach((section) => {
      section.layout.forEach((field) => {
        if (!(field.name === "created_at" || field.name === "updated_at")) {
          data[field.name] = null;
        }
        if (field.name === "is_admin") {
          data[field.name] = false;
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

const hasError = computed(() => (field) => {
  return validationErrors.value.some((item) => item.field === field.name);
});

const avatarField = computed(() => {
  return props.fields?.find((field) => field.name === "avatar") || null;
});

// Drives ImageField's initials fallback while the user is still typing,
// before an avatar has been uploaded.
const fullName = computed(() => {
  return (
    [form.first_name, form.last_name].filter(Boolean).join(" ") ||
    form.username ||
    ""
  );
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
      onSuccess: (page) => {
        clearAllAlerts();
        success(t("modules.actions.save_success"));
        confirmSendSetPasswordEmail(page.props.record?.id);
      },
      onError: () => {
        clearAllAlerts();
        Object.entries(form.errors).forEach(([field, message]) => {
          error(message);
        });
        console.error(form.errors);
      },
    });
};

// The user is created with no usable password (see UserController::store),
// so once the record exists we ask the admin whether to email them a link
// to set one. Create-only - editing an existing user never re-prompts.
const confirmSendSetPasswordEmail = async (userId) => {
  if (!userId) return;

  const ok = await confirm({
    title: t("modules.users.actions.send_set_password_title"),
    message: t("modules.users.actions.send_set_password_confirm"),
    confirmText: t("modules.users.actions.send_set_password_yes"),
    cancelText: t("modules.users.actions.send_set_password_no"),
  });

  if (!ok) return;

  router.post(
    `/users/${userId}/send-set-password`,
    {},
    {
      onSuccess: () => {
        clearAllAlerts();
        success(t("modules.users.actions.set_password_email_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.users.actions.set_password_email_error"));
      },
    },
  );
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
</script>

<template>
  <Head>
    <title>{{ module.label }} - Cubrel</title>
  </Head>

  <div class="record-layout" :style="{ '--module-color': module_color }">
    <div class="record-layout__header">
      <div class="record-layout__header__details">
        <div class="record-layout__header__details__info">
          <FieldRenderer
            v-if="avatarField"
            :field="avatarField"
            v-model="form.avatar"
            mode="edit"
            :module-color="module_color"
            :related_label="fullName"
          />
          <div class="record-layout__header__details__info__text">
            <div class="record-layout__header__details__info__text__name">
              <!-- {{ $t("modules.actions.create_new") }} -->
              {{ `${form.first_name ?? ""} ${form.last_name ?? ""}` }}
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
        <template v-for="s in recordLayout.sections" :key="s.name">
          <div class="record-layout__sections__item">
            <div class="record-layout__sections__item__title">
              {{ s.name }}
            </div>

            <div class="record-layout__sections__item__layout">
              <div
                v-for="f in s.layout.filter((f) => !getField(f).readonly)"
                :key="f.name"
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
                  mode="edit"
                  :module-color="module_color"
                  :has-error="hasError(f)"
                />
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
