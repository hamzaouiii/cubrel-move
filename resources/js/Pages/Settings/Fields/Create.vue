<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import { getCurrentInstance, computed, watch } from "vue"; // ADD watch import
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import Checkbox from "@/Pages/Components/Settings/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/Settings/FiledTypes/DropdownField.vue";

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  item: Object,
  field_types: Array,
  field: Object, // Assuming this is an array of field names
});

const page = usePage();
const appSettings = page.props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

// Initialize form with default values
const default_values = {
  name: "",
  key: "", // This will be auto-generated
  type: "",
  label: "",
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

const generatedKey = computed(() => {
  if (!form.name) return "";

  return form.name
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
});

watch(
  () => form.name,
  (newName) => {
    if (newName) {
      form.key = generatedKey.value;
    }
  },
  { immediate: true }
);

// Helper functions
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
  return field === "key";
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

  // Make sure key is set before submitting
  if (!form.key && form.name) {
    form.key = generatedKey.value;
  }

  form.post(page.url, {
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("fields.field_create_success"));
    },
    onError: () => {
      error(t("fields.field_create_error"));
    },
  });
};

const resetForm = () => {
  form.reset();
  warning(t("fields.field_reset_success"));
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
            <input type="text" :name="fieldName" v-model="form.key" disabled />
          </template>

          <template v-else-if="isCheckbox(fieldName)">
            <Checkbox v-model="form[fieldName]"></Checkbox>
          </template>

          <template v-else-if="isDropDown(fieldName)">
            <DropdownField
              v-model="form[fieldName]"
              :options="typesList()"
            ></DropdownField>
          </template>

          <template v-else>
            <input type="text" v-model="form[fieldName]" :name="fieldName" />
          </template>
        </div>

        <div class="settings__module__edit__actions">
          <button
            class="settings__module__edit__actions__reset btn"
            @click="resetForm"
            :disabled="!form.isDirty"
            type="button"
          >
            {{ $t("settings.cancel") }}
          </button>

          <button
            type="submit"
            class="settings__module__edit__actions__save btn"
            :disabled="!form.isDirty"
          >
            {{ $t("settings.save") }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
