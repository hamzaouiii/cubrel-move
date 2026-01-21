<script setup>
import { computed, ref, watch, getCurrentInstance } from "vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";

import Layout from "@/Layouts/Layout.vue";

import LayoutListEditor from "@/Pages/Components/Settings/LayoutListEditor.vue";
import LayoutRecordEditor from "@/Pages/Components/Settings/LayoutRecordEditor.vue";
import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";
import ModuleSettingBreadcrumbs from "@/Pages/Components/Settings/ModuleSettingBreadcrumbs.vue";

import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

const { success, error, info, clearAllAlerts } = useAlerts();
const { proxy } = getCurrentInstance();
const t = proxy.$t;

defineOptions({
  layout: Layout,
});

const props = defineProps({
  module: Object,
  type: String,
  defaultLayout: Object,
  fields: Object,
});
const page = usePage();
const appSettings = page.props.appSettings;
const listColumns = ref([]);
const recordSections = ref([]);

// setup layouts + record
const currentLayout = computed(() => {
  const custom = props.module.layouts?.find(
    (layout) => layout.type === props.type,
  );
  return custom?.definition || props.defaultLayout || null;
});

const moduleFields = computed(() => {
  return props.fields ?? [];
});

const fieldByName = computed(() => {
  const map = {};
  for (const field of moduleFields.value) {
    if (field?.name) map[field.name] = field;
  }
  return map;
});

// list layout
const listLayoutColumnConfigs = computed(() => {
  return Object.values(currentLayout?.value?.columns || {});
});

const selectedListColumnsFromDb = computed(() => {
  return listLayoutColumnConfigs.value
    .map((col) => {
      const field = fieldByName.value[col?.name];
      if (!field) return null;

      return {
        ...col,
        field,
        label: col.label ?? field.label ?? col.name,
      };
    })
    .filter(Boolean);
});

watch(
  selectedListColumnsFromDb,
  (val) => {
    listColumns.value = [...val];
  },
  { immediate: true },
);
const availableListFields = computed(() => {
  if (props.type !== "list") return [];

  const usedKeys = new Set(
    listColumns.value.map((col) => col?.name).filter(Boolean),
  );
  return moduleFields.value.filter((field) => !usedKeys.has(field.name));
});

const cleanedListColumns = computed(() =>
  listColumns.value.map((col) => {
    const { field, ...rest } = col || {};
    return rest;
  }),
);

const cleanedColumnsFromDb = computed(() =>
  listLayoutColumnConfigs.value.map((col) => {
    const { field, ...rest } = col || {};
    return rest;
  }),
);

// record layout
const recordLayoutSectionConfigs = computed(() => {
  if (props.type !== "record") return [];
  return Object.values(currentLayout?.value?.sections || {});
});

const recordLayoutFromDB = computed(() => {
  if (props.type !== "record") return [];

  return recordLayoutSectionConfigs.value.map((section) => {
    const layout = (section.layout || [])
      .map((col) => {
        if (!col?.name) return null;

        const field = fieldByName.value[col.name];
        if (!field) return null;

        return {
          ...col,
          field,
          label: col.label ?? field.label ?? col.name,
        };
      })
      .filter(Boolean);

    return {
      ...section,
      layout,
    };
  });
});

const cloneRecordSectionsFromDb = (sections) =>
  (sections || []).map((section) => ({
    ...section,
    layout: (section.layout || []).map((col) => ({ ...col })),
  }));

watch(
  recordLayoutFromDB,
  (val) => {
    recordSections.value = cloneRecordSectionsFromDb(val);
  },
  { immediate: true },
);

const cleanedRecordSections = computed(() =>
  recordSections.value.map((section) => ({
    ...section,
    layout: (section.layout || []).map((col) => {
      const { field, ...rest } = col || {};
      return rest;
    }),
  })),
);

const availableRecordFields = computed(() => {
  if (props.type !== "record") return [];

  const usedFields = new Set();
  recordSections.value.forEach((section) => {
    (section.layout || []).forEach((col) => {
      if (col?.name) usedFields.add(col.name);
    });
  });

  return moduleFields.value.filter((field) => !usedFields.has(field.name));
});
// both
const isDirty = computed(() => {
  if (props.type === "list") {
    const current = JSON.stringify(cleanedListColumns.value);
    const original = JSON.stringify(cleanedColumnsFromDb.value);
    return current !== original;
  } else if (props.type === "record") {
    const current = JSON.stringify(cleanedRecordSections.value);
    const original = JSON.stringify(recordLayoutSectionConfigs.value);
    return current !== original;
  }
  return false;
});

const form = useForm({
  module_id: props.module.id,
  type: props.type,
  definition:
    currentLayout.value ||
    (props.type === "list" ? { columns: [] } : { sections: [] }),
});

const resetToDatabaseValue = () => {
  if (props.type === "list") {
    listColumns.value = [...selectedListColumnsFromDb.value];
  } else if (props.type === "record") {
    recordSections.value = cloneRecordSectionsFromDb(recordLayoutFromDB.value);
  }
  form.definition = currentLayout.value || {};
  form.clearErrors();

  success(t("layouts.layout_reset_success"));
};

const saveLayout = () => {
  info(t("layouts.saving"));
  let definition = { ...(currentLayout.value || {}) };

  if (props.type === "list") {
    definition.columns = cleanedListColumns.value;
  } else if (props.type === "record") {
    definition.sections = cleanedRecordSections.value;
  }
  form.definition = definition;
  const url = page.url;

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      form.clearErrors();
      clearAllAlerts();
      const flash = usePage().props.flash;
      if (flash?.success) {
        success(flash.success);
      } else {
        success(t("layouts.layout_update_success"));
      }
    },
    onError: (errors) => {
      clearAllAlerts();
      const firstError = Object.values(errors)[0];
      error(firstError || "An error occurred while saving the layout.");
    },
  });
};

const layoutsUrl = () => {
  const key = props.type;
  const url = page.url;
  const segments = url.split("/").filter(Boolean);
  if (segments.at(-1) === key) {
    segments.pop();
  }
  const u = ("/" + segments.join("/")).toString();
  return u;
};

// useUnsavedChangesGuard({
//   getIsDirty: () => isDirty.value,
// });
</script>

<template>
  <Head>
    <title>
      {{ $t("layouts." + type) }} > {{ module.label }} >
      {{ $t("layouts.label") }} > {{ $t("settings.label") }}
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
    <div class="settings__module">
      <ModuleSettingTabs
        :setting-module="module"
        active-key="layouts"
      ></ModuleSettingTabs>
    </div>
    <div class="settings__module__header">
      <Link :href="layoutsUrl()">
        <i class="fa-solid fa-arrow-left"></i>
        {{ $t("layouts.back_to_list") }}</Link
      >
    </div>
    <div class="layouts__editor">
      <div v-if="type === 'list'">
        <LayoutListEditor
          v-model:columns="listColumns"
          :available-fields="availableListFields"
        />
      </div>

      <div v-if="type === 'record'">
        <LayoutRecordEditor
          v-model:sections="recordSections"
          :available-fields="availableRecordFields"
          :field-by-key="fieldByName"
        />
      </div>

      <div class="layouts__editor__actions">
        <button
          @click="resetToDatabaseValue()"
          class="layouts__editor__actions__reset btn"
          type="reset"
          :disabled="!isDirty || form.processing"
        >
          {{ form.processing ? $t("layouts.resetting") : $t("layouts.reset") }}
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
          class="layouts__editor__actions__save btn"
        >
          {{
            form.processing ? $t("layouts.saving") : $t("layouts.save_layout")
          }}
        </button>
      </div>
    </div>
  </div>
</template>
