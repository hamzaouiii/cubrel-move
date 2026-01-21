<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import { getCurrentInstance, computed, watch } from "vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { success, error, info, warning, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  item: Object,
  field_types: Array,
  field: Object,
});
const page = usePage();
const appSettings = page.props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const default_values = {
  label: "",
  name: "",
  key: "",
  type: "",
  readonly: false,
  hidden: false,
  nullable: false,
  required: false,
  searchable: false,
  filterable: false,
  sortable: false,
  default_value: "",
  options: "",
  min_length: "",
  max_length: "",
  regex: "",
};

const form = useForm({ ...default_values });

const generatedName = computed(() => {
  if (!form.label) return "";

  const name = form.label
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^-+|-+$/g, "");

  return name;
});

watch(
  () => form.label,
  (newName) => {
    if (newName) {
      form.name = generatedName.value;
    }
  },
  { immediate: true },
);

const generatedKey = computed(() => {
  return props.module.slug + "_" + generatedName.value;
});

watch(
  () => form.name,
  (newKey) => {
    if (newKey) {
      form.key = generatedKey.value;
    }
  },
  { immediate: true },
);

const isCheckbox = (field) => {
  const checkboxFields = [
    "readonly",
    "hidden",
    "nullable",
    "required",
    "searchable",
    "filterable",
    "sortable",
  ];
  return checkboxFields.includes(field);
};

const isDropDown = (field) => {
  return field === "type";
};

const isReadonly = (field) => {
  return field === "name";
};

const typesList = () => {
  return props.field_types.map((type) => ({
    value: type,
    label: t(`fields.types.${type}`),
  }));
};

const fieldsUrl = () => {
  const url = page.url;
  const segments = url.split("/").filter(Boolean);
  if (segments.at(-1) === "create") {
    segments.pop();
  }
  return "/" + segments.join("/");
};

const saveField = () => {
  info(t("settings.saving"));

  if (!form.key && form.name) {
    form.key = generatedKey.value;
    form.name = generatedName.value;
  }

  form.post(page.url, {
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("fields.field_create_success"));
      router.visit(fieldsUrl());
    },
    onError: (Error) => {
      clearAllAlerts();
      if (Error.table_missing) {
        error(Error.table_missing);
      } else if (Error) {
        for (const [key, value] of Object.entries(Error)) {
          error(key + " : " + value);
        }
      } else {
        error(t("fields.field_create_error"));
      }
    },
  });
};

const resetForm = () => {
  form.reset();
  router.visit(fieldsUrl());
  warning(t("fields.field_reset_success"));
};
const isDirty = () => {
  return form.label?.length >= 4 && form.type;
};

const displayKeyError = () => {
  return form.errors.key || form.errors.name;
};
</script>

<template>
  <Head>
    <title>
      {{ $t("fields.create_new_field") }} - {{ module.label }} -
      {{ $t("fields.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>

  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="module"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>

    <ModuleSettingTabs
      :setting-module="module"
      active-key="fields"
    ></ModuleSettingTabs>

    <div class="settings__module__header">
      <Link :href="fieldsUrl()">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("fields.back_to_list") }}
      </Link>
    </div>

    <div class="settings__module__edit">
      <form @submit.prevent="saveField">
        <!-- Assuming props.field is an array of field names -->
        <div
          class="settings__module__edit__element"
          v-for="fieldName in field"
          :key="fieldName"
        >
          <label> {{ $t("fields.metadata." + fieldName) }} </label>

          <template v-if="isReadonly(fieldName)">
            <input
              type="text"
              :name="fieldName"
              v-model="form.name"
              :class="[
                'disabled',
                {
                  'settings__module__edit__element--error-field':
                    displayKeyError(),
                },
              ]"
            />
            <span
              v-if="displayKeyError()"
              class="settings__module__edit__element__error"
              >{{ $t("fields.key_is_taken_error") }}</span
            >
          </template>

          <template v-else-if="isCheckbox(fieldName)">
            <Checkbox v-model="form[fieldName]"></Checkbox>
            <span
              v-if="form.errors[fieldName]"
              class="settings__module__edit__element__error"
            >
              {{ form.errors[fieldName] }}
            </span>
          </template>

          <template v-else-if="isDropDown(fieldName)">
            <DropdownField
              v-model="form[fieldName]"
              :options="typesList()"
            ></DropdownField>
            <span
              v-if="form.errors[fieldName]"
              class="settings__module__edit__element__error"
            >
              {{ form.errors[fieldName] }}
            </span>
          </template>

          <template v-else>
            <input
              type="text"
              v-model="form[fieldName]"
              :name="fieldName"
              :class="{
                'settings__module__edit__element--error-field':
                  form.errors[fieldName],
              }"
            />
            <span
              v-if="form.errors[fieldName]"
              class="settings__module__edit__element__error"
            >
              {{ form.errors[fieldName] }}
            </span>
          </template>
        </div>

        <div class="settings__module__edit__actions">
          <button
            class="settings__module__edit__actions__reset btn"
            @click="resetForm"
            :disabled="!isDirty()"
            type="button"
          >
            {{ $t("settings.cancel") }}
          </button>

          <button
            type="submit"
            class="settings__module__edit__actions__save btn"
            :disabled="!isDirty()"
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
