<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, useForm, router } from "@inertiajs/vue3";
import {
  computed,
  ref,
  watch,
  onMounted,
  onBeforeUnmount,
  reactive,
  getCurrentInstance,
} from "vue";
import { formatDateTime } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

import ModuleDropdownField from "../Components/FiledTypes/ModuleDropdownField.vue";

const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();
defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  title: String,
  record: Object,
  recordLayout: Object,
  dropdownLists: Object,
  fields: Object,
});

console.log(props.recordLayout);
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const form = useForm({ ...props.record });
const isEditing = ref(false);
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);

const editableRecord = reactive({ ...props.record });

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

const isDirty = computed(() => form.isDirty);
const enableEditing = () => {
  isEditing.value = true;
};

const getChangedData = (original, form) => {
  const changed = {};
  const edited = form.data();
  for (const key of Object.keys(edited)) {
    if (original[key] !== edited[key]) {
      changed[key] = edited[key];
    }
  }

  return changed;
};

const saveRecord = () => {
  info(t("modules.actions.updating"));
  const payload = getChangedData(props.record, form);

  if (Object.keys(payload).length === 0) {
    isEditing.value = false;
    return;
  }

  const moduleSlug = props.module.slug ?? props.module;
  const url = `/${moduleSlug}/${props.record.id}`;
  form
    .transform((data) => {
      const payload = { ...data };

      for (const section of props.recordLayout.sections) {
        for (const f of section.layout) {
          if (f.type === "dateTime" && payload[f.name]) {
            payload[f.name] = payload[f.name] + " 00:00:00";
          }
        }
      }

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
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
  window.addEventListener("keydown", handleKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
  window.removeEventListener("keydown", handleKeydown);
});

const appSettings = usePage().props.appSettings;

const displayValueFor = (f) => {
  const val = props.record[f.name];
  if (val == null || val === "") return "";

  if (f.type === "dateTime") {
    return formatDateTime(val, appSettings);
  }
  if (f.type === "longText") {
    if (val.length > 62) {
      return val.substring(0, 64) + "...";
    }
  }
  if (f.type === "dropDownField") {
    return getDropDownListLabel(f);
  }
  return val;
};

const getTextareaRows = (f) => {
  if (form[f.key]) {
    const val = form[f.key].split(" ").length;
    return val / 8;
  }
  return 5;
};
const isDropDown = (f) => {
  return f.type === "dropDownField";
};

useUnsavedChangesGuard({
  getIsDirty: () => isDirty.value,
});

const getFieldDropDownList = (f) => {
  const field = props.fields.find((field) => field.name === f.name);
  const list = props.dropdownLists.find((l) => l.field_key === field.key);

  return list.values;
};

const getDropDownListLabel = (f) => {
  const list = getFieldDropDownList(f);
  const label = list.find((l) => l.value === form[f.name])?.label || "-";
  return t(label);
};

watch(
  () => props.record,
  (data) => {
    if (!data) return;

    for (const section of props.recordLayout.sections) {
      for (const f of section.layout) {
        const value = data[f.name];

        if (!value) {
          form[f.name] = null;
          continue;
        }

        if (f.type === "date") {
          form[f.name] = value.slice(0, 10);
        }

        if (f.type === "dateTime") {
          form[f.name] = value.replace(" ", "T").slice(0, 16);
        }

        if (!["date", "dateTime"].includes(f.type)) {
          form[f.name] = value;
        }
      }
    }
  },
  { immediate: true, deep: true }
);
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
    <div class="module-layout__header">
      <div class="module-layout__header__details">
        <h3 class="module-layout__header__details__title">
          {{ record.name }}
        </h3>
      </div>
      <div class="module-layout__header__actions" ref="actionDropDownref">
        <div class="module-layout__header__actions__edit">
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
            class="module-layout__header__actions__edit__dropdown-btn"
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
              class="module-layout__header__actions__edit__dropdown show"
            >
              <li
                class="module-layout__header__actions__edit__dropdown__item disabled"
              >
                <i class="fa-solid fa-share-from-square"></i>
                <span>{{ $t("modules.actions.share") }}</span>
              </li>
              <li
                class="module-layout__header__actions__edit__dropdown__item disabled"
              >
                <i class="fa-solid fa-download"></i>
                <span>{{ $t("modules.actions.export") }}</span>
              </li>
              <li class="module-layout__header__actions__edit__dropdown__item">
                <i class="fa-solid fa-hourglass-end"></i>
                <span>{{ $t("modules.actions.placeholder") }}</span>
              </li>
              <li class="module-layout__header__actions__edit__dropdown__item">
                <i class="fa-solid fa-file-pdf"></i>
                <span>{{ $t("modules.actions.bulk_action") }}</span>
              </li>
              <li
                @click="deleteRecord()"
                class="module-layout__header__actions__edit__dropdown__item"
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

    <div class="module-layout__content">
      <div
        class="module-layout__content__section"
        v-for="s in recordLayout.sections"
      >
        <div class="module-layout__content__section__title">
          {{ s.name }}
        </div>
        <div class="module-layout__content__section__layout">
          <div
            v-for="f in s.layout"
            class="module-layout__content__section__layout__field"
          >
            <span class="module-layout__content__section__layout__field__label">
              {{ $t(f.label) }}:
            </span>

            <div
              v-if="!isEditing"
              :class="[
                'module-layout__content__section__layout__field__content',
                { 'view-uneditable-field': f.readonly },
              ]"
              @click="!f.readonly && enableEditing()"
            >
              <span>
                {{ displayValueFor(f) }}
              </span>
            </div>
            <div
              :class="[
                'module-layout__content__section__layout__field__content',
                'editing-mode',
                { 'uneditable-field': f.readonly },
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
                ></ModuleDropdownField>
              </template>
              <template v-else-if="f.type == 'longText'">
                <textarea
                  v-model="form[f.name]"
                  :rows="getTextareaRows(f)"
                ></textarea>
              </template>
              <template v-else-if="f.type == 'dateTime'">
                <input type="datetime-local" v-model="form[f.name]" />
              </template>
              <template v-else>
                <input type="text" v-model="form[f.name]" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
