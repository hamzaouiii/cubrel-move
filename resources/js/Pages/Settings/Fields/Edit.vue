<script setup>
import Layout from "@/Layouts/Layout.vue";
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import { getCurrentInstance, toRef, watch } from "vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useFieldRules } from "@/Composables/useFieldRules";

import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import DropdownField from "@/Pages/Components/FiledTypes/SettingDropdownField.vue";

const { success, error, info, warning, clearAllAlerts } = useAlerts();

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  metadata: Object,
  item: Object,
  field_types: Array,
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;

/**
 * Initialize form with existing metadata.
 * Note: We use props.metadata[field] to ensure we match the keys
 * coming from your Laravel model's getFieldMetadata()
 */
const form = useForm({
  name: props.metadata.name,
  type: props.metadata.type,
  label: t(props.metadata.label),
  readonly: !!props.metadata.readonly,
  hidden: !!props.metadata.hidden,
  required: !!props.metadata.required,
  searchable: !!props.metadata.searchable,
  filterable: !!props.metadata.filterable,
  sortable: !!props.metadata.sortable,
  default_value: props.metadata.default_value || "",
  min_length: props.metadata.min_length || "",
  max_length: props.metadata.max_length || "",
  regex: props.metadata.regex || "",
});

/**
 * COMPOSABLE INTEGRATION
 * Destructuring centralized helpers.
 * Note: passed 'true' to isReadonly in the template for Edit Mode logic.
 */
const { visibleMetadata, applyRules, isCheckbox, isDropDown, isReadonly } =
  useFieldRules(form, toRef(props, "metadata"));

// Apply rules whenever type changes
watch(
  () => form.type,
  (newType) => {
    applyRules(newType);
  },
);

const typesList = () => {
  return props.field_types.map((type) => ({
    value: type,
    label: t(`fields.types.${type}`),
  }));
};

const fieldsUrl = () => {
  const key = props.metadata?.name || "";
  const segments = page.url.split("/").filter(Boolean);
  if (segments.at(-1) === key) {
    segments.pop();
  }
  return "/" + segments.join("/");
};

const saveField = () => {
  info(t("settings.saving"));
  form.put(page.url, {
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("fields.field_update_success"));
      router.visit(fieldsUrl());
    },
    onError: () => {
      clearAllAlerts();
      error(t("fields.field_update_error"));
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
      {{ $t(metadata.label) }} - {{ module.label }} - {{ $t("fields.label") }} -
      {{ $t("settings.label") }}
    </title>
  </Head>

  <div
    class="settings"
    :style="{ '--primary-color': appSettings.primary_color }"
  >
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs :setting-module="module" />
      </div>
    </div>

    <ModuleSettingTabs :setting-module="module" active-key="fields" />

    <div class="settings__module__header">
      <Link :href="fieldsUrl()">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("fields.back_to_list") }}
      </Link>
    </div>

    <div class="settings__module__edit">
      <form @submit.prevent="saveField">
        <div
          v-for="fieldName in visibleMetadata"
          :key="fieldName"
          class="settings__module__edit__element"
        >
          <label class="settings__module__edit__element__label">
            {{ $t("fields.metadata." + fieldName) }}
          </label>

          <div class="settings__module__edit__element__content">
            <template v-if="isReadonly(fieldName, true)">
              <input type="text" v-model="form[fieldName]" disabled />
            </template>

            <template v-else-if="isCheckbox(fieldName)">
              <Checkbox v-model="form[fieldName]" />
              <span
                v-if="form.errors[fieldName]"
                class="settings__module__edit__element__error"
              >
                {{ form.errors[fieldName] }}
              </span>
            </template>

            <template v-else-if="isDropDown(fieldName)">
              <DropdownField v-model="form[fieldName]" :options="typesList()" />
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

        <div class="settings__module__edit__actions">
          <button
            type="button"
            class="settings__module__edit__actions__reset btn"
            @click="resetForm"
            :disabled="!form.isDirty"
          >
            {{ $t("settings.reset") }}
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
