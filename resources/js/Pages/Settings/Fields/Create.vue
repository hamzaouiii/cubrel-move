<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";

import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import {
  getCurrentInstance,
  computed,
  watch,
  ref,
  onMounted,
  onBeforeUnmount,
  toRef,
} from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import { useFieldRules } from "@/Composables/useFieldRules";

import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";

import DropdownSelector from "@/Pages/Components/Settings/Dropdowns/DropdownSelector.vue";
import CreateNewDropdownListModal from "@/Pages/Components/Settings/Dropdowns/CreateNewDropdownListModal.vue";
import EditDropdownListModal from "@/Pages/Components/Settings/Dropdowns/EditDropdownListModal.vue";

const { success, error, info, warning, clearAllAlerts } = useAlerts();

defineOptions({
  layout: AppLayout,
});

const props = defineProps({
  module: Object,
  field_types: Array,
  metadata: Object,
});

const page = usePage();
const appSettings = page.props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const selected_dropdown_list = ref(null);
const DropDownListOptions = ref([]);

const default_values = {
  label: "",
  name: "",
  key: "",
  type: "",
  dropdown_list: "",
  readonly: false,
  required: false,
  searchable: false,
  filterable: false,
  sortable: false,
  hidden: false,
  default_value: "",
  min_length: "",
  max_length: "",
  regex: "",
};

const form = useForm({ ...default_values });

/**
 * COMPOSABLE INTEGRATION
 * Using the centralized rules and UI helpers
 */
const { visibleMetadata, applyRules, isCheckbox, isDropDown, isReadonly } =
  useFieldRules(form, toRef(props, "metadata"));

const generatedName = computed(() => {
  if (!form.label) return "";

  const name = form.label
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/ä/g, "ae")
    .replace(/ö/g, "oe")
    .replace(/ü/g, "ue")
    .replace(/ß/g, "ss")
    .replace(/[^a-z0-9]+/g, "_")
    .replace(/^_+|_+$/g, "");

  return name;
});

const generatedKey = computed(() => {
  return props.module.slug + "_" + generatedName.value;
});

// Watchers
watch(
  () => form.type,
  (newType) => {
    applyRules(newType);
  },
);

watch(
  () => form.label,
  (newName) => {
    if (newName) {
      form.name = generatedName.value;
    }
  },
  { immediate: true },
);

watch(
  () => form.name,
  (newName) => {
    if (newName) {
      form.key = generatedKey.value;
    }
  },
  { immediate: true },
);

// UI Helpers
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

// Form Actions
const saveField = () => {
  info(t("settings.saving"));

  if (!form.key && form.name) {
    form.key = generatedKey.value;
    form.name = generatedName.value;
    form.label = form?.label.trim();
  }
  if (form.type === "select") {
    form.dropdown_list = selected_dropdown_list.value;
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
      } else {
        for (const [key, value] of Object.entries(Error)) {
          error(value);
        }
      }
    },
  });
};

const resetForm = () => {
  form.reset();
  router.visit(fieldsUrl());
  warning(t("fields.field_reset_success"));
};
const isDirty = ref(false);

watch(
  form,
  () => {
    isDirty.value = form.label?.length >= 4 && form.type;
  },
  { deep: true },
);

const displayKeyError = () => {
  return form.errors.key || form.errors.name;
};

// Dropdown Modal Logic
const openCreateDialog = () => (showCreateDialog.value = true);
const openEditDialog = () => (showEditDialog.value = true);
const closeCreateDialog = () => (showCreateDialog.value = false);
const closeEditDialog = () => (showEditDialog.value = false);

const fetchDrodownList = async () => {
  try {
    const { data } = await axios.get("/api/dropdown-lists", {});
    DropDownListOptions.value = data.list;
  } catch (error) {
    console.error("Failed to fetch dropdown lists:", error);
  }
};

const assignList = (value) => {
  DropDownListOptions.value.push(value);
  selected_dropdown_list.value = value.id;
};

const getDropdonwItem = (id) => {
  return DropDownListOptions.value.find((e) => e.id === id);
};

onMounted(() => {
  fetchDrodownList();
});

useUnsavedChangesGuard({
  getIsDirty: () => isDirty,
});
const moduleColor = computed(() =>
  appSettings.use_individual_module_colors
    ? props.module.color
    : appSettings.primary_color,
);
</script>

<template>
  <Head>
    <title>
      {{ $t("fields.create_new_field") }} - {{ module.label }} -
      {{ $t("fields.label") }} - {{ $t("settings.label") }} - Cubrel
    </title>
  </Head>

  <div
    class="settings"
    :style="
      ({ '--primary-color': appSettings.primary_color },
      { '--module-color': moduleColor })
    "
  >
    <div class="settings__module">
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
        <form class="settings__module__edit__form" @submit.prevent="saveField">
          <div
            class="settings__module__edit__element"
            v-for="fieldName in visibleMetadata"
            :key="fieldName"
          >
            <label class="settings__module__edit__element__label">
              {{ $t("fields.metadata." + fieldName) }}
            </label>
            <div class="settings__module__edit__element__content">
              <template v-if="isReadonly(fieldName)">
                <input
                  type="text"
                  :name="fieldName"
                  v-model="form[fieldName]"
                  disabled
                  :class="{
                    'settings__module__edit__element--error-field':
                      displayKeyError(),
                  }"
                />
                <span
                  v-if="displayKeyError()"
                  class="settings__module__edit__element__error"
                >
                  {{ $t("fields.key_is_taken_error") }}
                </span>
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
                  :hasError="form.errors[fieldName]"
                ></DropdownField>

                <transition name="dropdown-fade">
                  <div
                    class="dropdown-selector"
                    v-if="form[fieldName] === 'select'"
                  >
                    <DropdownSelector
                      v-model="selected_dropdown_list"
                      :options="DropDownListOptions"
                      @onOpenCreateDialog="openCreateDialog"
                      @onOpenEditDialog="openEditDialog"
                    />
                  </div>
                </transition>

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
          </div>

          <div class="settings__actions">
            <button
              type="button"
              class="settings__actions__reset"
              @click="resetForm"
              :disabled="!isDirty"
            >
              {{ $t("settings.reset") }}
            </button>

            <button
              type="submit"
              class="settings__actions__save"
              :disabled="!isDirty"
            >
              {{ $t("settings.save") }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <CreateNewDropdownListModal
    @onCloseModal="closeCreateDialog"
    @listCreated="assignList"
    v-if="showCreateDialog"
  />

  <EditDropdownListModal
    :dropdown="getDropdonwItem(selected_dropdown_list)"
    @onCloseModal="closeEditDialog"
    v-if="showEditDialog"
  />
</template>
