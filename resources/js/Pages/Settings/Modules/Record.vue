<script setup>
import { reactive, computed, getCurrentInstance } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import { Head, usePage, useForm } from "@inertiajs/vue3";
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import ModuleSettingsHeader from "@/Pages/Components/Settings/ModuleSettingsHeader.vue";

import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import ExplainTip from "@/Pages/Components/Globals/ExplainTip.vue";
import LongText from "@/Pages/Components/FiledTypes/LongText.vue";
const appSettings = usePage().props.appSettings;

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const { success, error, info, clearAllAlerts } = useAlerts();
defineOptions({
  layout: [AppLayout, SettingsLayout],
});

const props = defineProps({
  settingModule: Object,
  categoryList: Object,
  moduleOptions: { type: Array, default: () => [] },
  lineItemSourceLocked: { type: Boolean, default: false },
});
const form = useForm({ ...props.settingModule });
const editableModule = reactive({
  display_label: "",
  single_label: "",
  ...props.settingModule,
});
editableModule.show_in_sidebar = Boolean(editableModule.show_in_sidebar);
editableModule.is_product_like = Boolean(editableModule.is_product_like);
editableModule.is_relatable = Boolean(editableModule.is_relatable);

const lineItemSourceOptions = computed(() => ({ values: props.moduleOptions }));

const noHintFields = ["description", "show_in_sidebar"];
const hintFor = (key) =>
  noHintFields.includes(key) ? null : `settings.modules.${key}_hint`;
const editableFields = computed(() => {
  const ignore = [
    "name",
    "id",
    "created_at",
    "updated_at",
    "can_view",
    "can_create",
    "can_edit",
    "can_delete",
    "path",
    "sort_order",
    "is_active",
    "is_custom",
    "table_name",
    "model_class",
    "slug",
    "handler_class",
    "label",
    "is_draft",
    "locked_by",
    "locked_until",
    "has_line_items",
    "line_item_source_module",
    "has_owner",
    "show_in_module_manager",
    "is_relatable",
    // for now this feature is only avialable in the module builder
    "is_activity",
    "has_activity",
  ];
  return Object.entries(editableModule).filter(
    ([key]) => !ignore.includes(key),
  );
});

const inputTypeFor = (key, value) => {
  if (key === "show_in_sidebar") return "checkbox";
  if (key === "display_label") return "display_label";
  if (key === "single_label") return "single_label";
  if (key === "category") return "select";
  if (key === "icon") return "icon";
  if (typeof value === "number") return "number";
  if (key === "color") return "color";
  if (key === "description") return "textarea";
  if (key === "is_product_like") return "checkbox";
  if (key === "is_relatable") return "checkbox";
  return "text";
};

const disableThis = (key) => {
  if (key === "display_label" || key === "single_label") return true;
  return false;
};
const isDirty = computed(() => {
  const fieldsDirty = editableFields.value.some(([key, value]) => {
    if (key === "display_label") {
      if (typeof value === "string") {
        return value.trim() !== "";
      }
      return !!value;
    }

    const original = props.settingModule[key];
    const current = editableModule[key];

    if (typeof original === "number" && typeof current === "boolean") {
      return Boolean(original) !== current;
    }

    return original !== current;
  });

  // Excluded from editableFields (rendered separately above), so it needs its
  // own dirty check to keep Save enabled when only this value changes.
  const sourceModuleDirty =
    editableModule.line_item_source_module !==
    props.settingModule.line_item_source_module;

  return fieldsDirty || sourceModuleDirty;
});

const saveRecord = () => {
  info(t("settings.saving"));
  const url = "/settings/modules/" + props.settingModule.id;
  const payload = editableModule;
  form
    .transform(() => payload)
    .put(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.module_update_success"));
      },
      onError: (errors) => {
        clearAllAlerts();
        const serverError = Object.values(errors)[0];
        error(t("settings.module_save_error") + ": " + serverError);
      },
    });
};

const resetForm = () => {
  Object.keys(editableModule).forEach((key) => {
    editableModule[key] = props.settingModule[key];
  });
};

useUnsavedChangesGuard({
  getIsDirty: () => isDirty.value,
});
</script>

<template>
  <Head>
    <title>
      {{ settingModule.label }} - {{ $t("settings.label") }} - Cubrel
    </title>
  </Head>

  <div
    class="settings"
    :style="[
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': editableModule.color },
      { '--primary-color': appSettings.primary_color },
    ]"
  >
    <div class="settings__module">
      <ModuleSettingsHeader
        :setting-module="settingModule"
        active-key="edit"
      ></ModuleSettingsHeader>
      <div class="settings__module__edit">
        <form class="settings__module__edit__form" @submit.prevent="saveRecord">
          <div
            v-for="[key, value] in editableFields"
            :key="key"
            class="settings__module__edit__element"
          >
            <label class="settings__module__edit__element__label">
              {{ $t("settings.modules." + key) }}
              <ExplainTip
                v-if="hintFor(key)"
                :text="t(hintFor(key))"
                :color="settingModule.color"
              />
            </label>
            <div class="settings__module__edit__element__content">
              <Checkbox
                v-if="inputTypeFor(key, value) === 'checkbox'"
                v-model="editableModule[key]"
                :module-color="editableModule.color"
              ></Checkbox>
              <IconPicker
                v-else-if="inputTypeFor(key, value) === 'icon'"
                v-model="editableModule[key]"
                :color="editableModule.color"
              />
              <input
                v-else-if="inputTypeFor(key, value) === 'display_label'"
                :class="{ disabled: disableThis(key) }"
                type="text"
                :disabled="disableThis(key)"
                v-model="settingModule.label"
              />
              <input
                v-else-if="inputTypeFor(key, value) === 'single_label'"
                :class="{ disabled: disableThis(key) }"
                type="text"
                :disabled="disableThis(key)"
                v-model="settingModule.single_label"
              />
              <Select
                v-else-if="inputTypeFor(key, value) === 'select'"
                :dropdown_list="categoryList"
                v-model="editableModule[key]"
                mode="module-builder"
              />
              <LongText
                v-else-if="inputTypeFor(key, value) === 'textarea'"
                v-model="editableModule[key]"
                mode="settings"
              ></LongText>
              <ColorPicker
                v-else-if="inputTypeFor(key, value) === 'color'"
                v-model="editableModule[key]"
              ></ColorPicker>
              <input
                v-else
                :type="inputTypeFor(key, value)"
                v-model="editableModule[key]"
              />
            </div>
          </div>

          <div
            v-if="settingModule.has_line_items"
            class="settings__module__edit__element"
          >
            <label class="settings__module__edit__element__label">
              {{ $t("settings.modules.line_item_source_module") }}
              <ExplainTip
                :text="
                  t(
                    lineItemSourceLocked
                      ? 'settings.modules.line_item_source_locked'
                      : 'settings.modules.line_item_source_module_hint',
                  )
                "
                :color="settingModule.color"
              />
            </label>
            <div class="settings__module__edit__element__content">
              <Select
                :dropdown_list="lineItemSourceOptions"
                v-model="editableModule.line_item_source_module"
                :disabled="lineItemSourceLocked"
                mode="module-builder"
              />
            </div>
          </div>

          <div class="settings__actions">
            <button
              @click="resetForm()"
              class="settings__actions__reset"
              type="reset"
              :disabled="!isDirty"
            >
              {{ $t("settings.reset") }}
            </button>

            <button
              class="settings__actions__save"
              type="submit"
              :disabled="!isDirty"
            >
              {{ $t("settings.save") }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
