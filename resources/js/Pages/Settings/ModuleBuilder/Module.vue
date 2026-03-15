<script setup>
import { reactive, computed, getCurrentInstance, onMounted } from "vue";
import Layout from "@/Layouts/Layout.vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import { useAlerts } from "@/Composables/useAlerts";
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";

import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";
import ModuleDetails from "@/Pages/Components/Settings/Builder/ModuleDetails.vue";
const appSettings = usePage().props.appSettings;
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { success, error, info, clearAllAlerts } = useAlerts();

defineOptions({ layout: Layout });

const props = defineProps({ module: Object });
const page = usePage();
const form = useForm({ ...props.module });

const editableModule = reactive({ display_label: "", ...props.module });
editableModule.show_in_sidebar = Boolean(editableModule.show_in_sidebar);

// Clean up the dummy data on initial load so the user sees blank fields to fill
onMounted(() => {
  if (editableModule.name === "New Module") editableModule.display_label = "";
  if (editableModule.slug.startsWith("draft_")) editableModule.slug = "";
});

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
    "handler_class",
    "label",
    "is_draft",
    "locked_by",
    "locked_until",
  ];
  // Note: 'slug' was removed from the ignore list so it renders for the user!
  return Object.entries(editableModule).filter(
    ([key]) => !ignore.includes(key),
  );
});

const inputTypeFor = (key, value) => {
  if (key === "show_in_sidebar") return "checkbox";
  if (key === "display_label") return "text";
  if (key === "slug") return "text";
  if (key === "icon") return "icon";
  if (typeof value === "number") return "number";
  if (key === "color") return "color";
  if (key === "description") return "textarea";
  return "text";
};

const isDirty = computed(() => {
  return editableFields.value.some(([key, value]) => {
    if (key === "display_label")
      return typeof value === "string" ? value.trim() !== "" : !!value;
    const original = props.module[key];
    const current = editableModule[key];
    if (typeof original === "number" && typeof current === "boolean")
      return Boolean(original) !== current;
    return original !== current;
  });
});

// Saves intermediate progress without publishing
const saveDraft = () => {
  info(t("settings.saving_draft"));
  const url = "/settings/modules/" + props.module.id;
  form
    .transform(() => editableModule)
    .put(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.draft_update_success"));
      },
      onError: (errors) => {
        clearAllAlerts();
        error(
          t("settings.module_save_error") + ": " + Object.values(errors)[0],
        );
      },
    });
};

// Finalizes the module
const publishModule = () => {
  info(t("settings.publishing_module"));
  const url = `/settings/modulebuilder/${props.module.id}/publish`;
  form
    .transform(() => editableModule)
    .post(url, {
      onSuccess: () => {
        clearAllAlerts();
        success(t("settings.module_publish_success"));
      },
      onError: (errors) => {
        clearAllAlerts();
        error(
          t("settings.module_publish_error") + ": " + Object.values(errors)[0],
        );
      },
    });
};

// useUnsavedChangesGuard({ getIsDirty: () => isDirty.value });
</script>

<template>
  <Head>
    <title>{{ $t("settings.module_builder") }}</title>
  </Head>

  <div
    class="settings"
    :style="[
      appSettings.use_individual_module_colors === '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': editableModule.color },
      { '--primary-color': appSettings.primary_color },
    ]"
  >
    <div class="settings__module">
      <div class="settings__module__edit">
        <ModuleDetails
          :editable-fields="editableFields"
          :editable-module="editableModule"
          :input-type-for="inputTypeFor"
          :publish-module="publishModule"
        />

        <div class="settings__actions">
          <button
            @click.prevent="saveDraft"
            class="settings__actions__resety"
            type="button"
            :disabled="!isDirty"
          >
            {{ $t("settings.modules.save_draft") }}
          </button>

          <button class="settings__actions__save" type="submit">
            {{ $t("settings.modules.publish_module") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
