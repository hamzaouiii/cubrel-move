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
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = usePage().props.appSettings;
const { validateFieldTypes } = useFieldValidation(props);

// State
const form = useForm({ ...props.record });
const isEditing = ref(false);
const validationErrors = ref([]);
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);
const currentTab = ref("overview");
const overlayOpen = ref(false);
const activePanel = ref(null);
const activeParentRecord = ref(null);
const expandPanel = ref(null);
// Computed
const avatar = computed(() => {
  const username = props.record?.username?.trim();
  if (!username) return "";

  const cleaned = username.replace(/\d+/g, "");
  const words = cleaned.split(/\s+/).filter(Boolean);

  if (words.length >= 2) {
    return ((words[0][0] ?? "") + (words[1][0] ?? "")).toUpperCase();
  }

  return (words[0]?.slice(0, 2) ?? "").toUpperCase();
});

const avatarField = computed(() => {
  return props.fields?.find((field) => field.name === "avatar") || null;
});

const currentUser = computed(() => usePage().props?.auth?.user);
const isRoot = computed(() => usePage().props?.auth?.user?.is_root);

const canImpersonate = computed(() => {
  if (!isRoot.value) return false;
  if (currentUser.value?.id === props.record?.id) return false;
  return props.record?.status === "active";
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

// SAVE RECORD
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

const sendPasswordReset = () => {
  showActionDropDown.value = false;
  info(t("modules.users.actions.sending_reset_password"));

  router.post(
    `/users/${props.record.id}/reset-password`,
    {},
    {
      onSuccess: () => {
        clearAllAlerts();
        success(t("modules.users.actions.reset_password_success"));
      },
      onError: () => {
        clearAllAlerts();
        error(t("modules.users.actions.reset_password_error"));
      },
    },
  );
};

const loginAsUser = () => {
  showActionDropDown.value = false;

  router.post(
    `/users/${props.record.id}/impersonate`,
    {},
    {
      onError: () => {
        clearAllAlerts();
        error(t("modules.users.actions.login_as_error"));
      },
    },
  );
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
  if (getField(f)?.readonly) return "detail";
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
</script>
<template>
  <Head>
    <title>{{ record.username }} - {{ title }} - Cubrel</title>
  </Head>

  <div class="record-layout" :style="{ '--module-color': module_color }">
    <div class="record-layout__header">
      <div class="record-layout__header__details">
        <div class="record-layout__header__details__info">
          <FieldRenderer
            v-if="isEditing && avatarField"
            :field="avatarField"
            v-model="form.avatar"
            mode="edit"
            :module-color="module_color"
          />
          <template v-else>
            <div
              class="record-layout__header__details__info__avatar-img"
              v-if="record.avatar"
            >
              <img :src="record.avatar" alt="" />
            </div>
            <div
              class="record-layout__header__details__info__avatar-text"
              v-else
            >
              {{ avatar }}
            </div>
          </template>
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
                  @click="sendPasswordReset()"
                  class="record-layout__header__details__actions__edit__dropdown__item"
                >
                  <i class="fa-solid fa-key"></i>
                  <span>{{
                    $t("modules.users.actions.send_reset_password_email")
                  }}</span>
                </li>
                <li
                  v-if="canImpersonate"
                  @click="loginAsUser()"
                  class="record-layout__header__details__actions__edit__dropdown__item"
                >
                  <i class="fa-solid fa-arrow-right-to-bracket"></i>
                  <span>{{ $t("modules.users.actions.login_as") }}</span>
                </li>
                <li
                  @click="deleteRecord()"
                  class="record-layout__header__details__actions__edit__dropdown__item record-layout__header__details__actions__edit__dropdown__item--delete"
                >
                  <i class="fa-solid fa-trash-can"></i>
                  <span>{{ $t("modules.actions.delete") }}</span>
                </li>
              </ul>
            </transition>
          </div>
        </div>
      </div>
    </div>
    <div class="record-layout__scroll">
      <div v-if="currentTab !== 'related'" class="record-layout__sections">
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
              @click="enableEditing()"
            >
              <span class="record-layout__sections__item__layout__field__label">
                {{ $t(f.label) }}:
              </span>
              <FieldRenderer
                :field="getField(f)"
                v-model="form[f.name]"
                :mode="getMode(f)"
                :read-only="getField(f)?.readonly"
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
