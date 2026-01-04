<script setup>
import { reactive, computed, getCurrentInstance } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import IconPicker from "@/Pages/Components/Settings/IconPicker.vue";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "@/Pages/Components/Settings/FiledTypes/Checkbox.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";
const appSettings = usePage().props.appSettings;

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const { success, error, info, clearAllAlerts } = useAlerts();
defineOptions({
  layout: Layout,
});

const props = defineProps({
  settingModule: Object,
});
const page = usePage();
const form = useForm({ ...props.settingModule });
const editableModule = reactive({ display_label: "", ...props.settingModule });
editableModule.show_in_sidebar = Boolean(editableModule.show_in_sidebar);
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
  ];
  return Object.entries(editableModule).filter(
    ([key]) => !ignore.includes(key)
  );
});

const inputTypeFor = (key, value) => {
  if (key === "show_in_sidebar") return "checkbox";
  if (key === "display_label") return "display_label";
  if (key === "icon") return "icon";
  if (typeof value === "number") return "number";
  if (key === "color") return "color";
  if (key === "description") return "textarea";
  return "text";
};

const disableThis = (key) => {
  if (key === "display_label") return true;
  return false;
};

const isDirty = computed(() => {
  return editableFields.value.some(([key, value]) => {
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
</script>

<template>
  <Head>
    <title>{{ settingModule.label }} - {{ $t("settings.label") }}</title>
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
    <div class="settings__header">
      <div class="settings__header__title">
        <ModuleSettingBreadcrumbs
          :setting-module="settingModule"
        ></ModuleSettingBreadcrumbs>
      </div>
    </div>
    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="settingModule"
        active-key="edit"
      ></ModuleSettingTabs>
      <div class="settings__module__edit">
        <form @submit.prevent="saveRecord">
          <div
            v-for="[key, value] in editableFields"
            :key="key"
            class="settings__module__edit__element"
          >
            <label>
              {{ $t("settings.modules." + key) }}
            </label>

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
            <textarea
              v-else-if="inputTypeFor(key, value) === 'textarea'"
              v-model="editableModule[key]"
            ></textarea>
            <input
              v-else
              :type="inputTypeFor(key, value)"
              v-model="editableModule[key]"
            />
          </div>

          <div class="settings__module__edit__actions">
            <button
              @click="resetForm()"
              class="settings__module__edit__actions__reset btn"
              type="reset"
              :disabled="!isDirty"
            >
              Reset
            </button>

            <button
              class="settings__module__edit__actions__save btn"
              type="submit"
              :disabled="!isDirty"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
