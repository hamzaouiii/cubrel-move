<script setup>
import { computed, ref, watch, getCurrentInstance } from "vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";

import Layout from "@/Layouts/Layout.vue";

import LayoutListEditor from "@/Pages/Components/Settings/Layouts/LayoutListEditor.vue";
import LayoutRecordEditor from "@/Pages/Components/Settings/Layouts/LayoutRecordEditor.vue";
import LayoutRelatedEditor from "@/Pages/Components/Settings/Layouts/LayoutRelatedEditor.vue";
import LayoutSubpanelEditor from "@/Pages/Components/Settings/Layouts/LayoutSubpanelEditor.vue";
import LayoutLinkingPanelEditor from "@/Pages/Components/Settings/Layouts/LayoutLinkingPanelEditor.vue";

import ModuleSettingTabs from "@/Pages/Components/Settings/ModuleSettingTabs.vue";

import { useAlerts } from "@/Composables/useAlerts";
import { useUnsavedChangesGuard } from "@/Composables/useUnsavedChangesGuard";

const { success, error, info, clearAllAlerts, warning } = useAlerts();
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
  relationships: Object,
});

const page = usePage();
const appSettings = page.props.appSettings;
const listColumns = ref([]);
const relatedColumns = ref([]);
const recordSections = ref([]);
const emptyColumns = ref(new Set());

// setup layouts + record + related
const currentLayout = computed(() => {
  const custom = props.module.layouts?.find(
    (layout) => layout.type === props.type,
  );
  return custom?.definition || props.defaultLayout || null;
});

const moduleFields = computed(() => {
  return props.fields ?? [];
});

const moduleRelationhsips = computed(() => {
  return props.relationships ?? [];
});

const fieldByName = computed(() => {
  const map = {};
  for (const field of moduleFields.value) {
    if (field?.name) map[field.name] = field;
  }
  return map;
});

const relatedByName = computed(() => {
  const map = {};
  for (const rel of moduleRelationhsips.value) {
    if (rel?.name) map[rel.name] = rel;
  }
  return map;
});

// list layout + linking panel
const listLayoutColumnConfigs = computed(() => {
  if (props.type === "list" || props.type === "linkingPanel") {
    return Object.values(currentLayout?.value?.columns || {}).filter(Boolean);
  }
  return [];
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
  if (props.type !== "list" && props.type !== "linkingPanel") {
    return [];
  }

  const usedKeys = new Set(
    listColumns.value.map((col) => col?.name).filter(Boolean),
  );

  return moduleFields.value
    .filter((field) => !usedKeys.has(field.name))
    .map((field) => ({
      name: field.name,
      label: field.label ?? field.name,
      type: field.type,
    }));
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

//related
const relatedLayoutColumnConfigs = computed(() => {
  if (props.type !== "related") return [];
  return Object.values(currentLayout?.value?.columns || {});
});

const relatedLayoutFromDB = computed(() => {
  if (props.type !== "related") return [];

  return relatedLayoutColumnConfigs.value.map((column) => {
    const layout = (column.layout || [])
      .map((col) => {
        if (!col?.name) return null;

        const rel = relatedByName.value[col.name];
        if (!rel) return null;

        return {
          ...col,
          label: col.label ?? rel.label ?? col.name,
        };
      })
      .filter(Boolean);

    return {
      ...column,
      layout,
    };
  });
});

const cloneRelatedColumnsFromDb = (columns) =>
  (columns || []).map((column) => ({
    ...column,
    layout: (column.layout || []).map((col) => ({ ...col })),
  }));

watch(
  relatedLayoutFromDB,
  (val) => {
    relatedColumns.value = cloneRelatedColumnsFromDb(val);
  },
  { immediate: true },
);

const cleanedRelatedColumns = computed(() =>
  relatedColumns.value.map((column) => ({
    ...column,
    layout: (column.layout || []).map((col) => {
      const { rel, ...rest } = col || {};
      return rest;
    }),
  })),
);

const availableRelationships = computed(() => {
  if (props.type !== "related") return [];

  const usedRelationships = new Set();
  relatedColumns.value.forEach((column) => {
    (column.layout || []).forEach((col) => {
      if (col?.name) usedRelationships.add(col.name);
    });
  });

  return moduleRelationhsips.value.filter(
    (rel) => !usedRelationships.has(rel.name),
  );
});
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
  if (props.type === "list" || props.type === "linkingPanel") {
    const current = JSON.stringify(cleanedListColumns.value);
    const original = JSON.stringify(cleanedColumnsFromDb.value);
    return current !== original;
  } else if (props.type === "record") {
    const current = JSON.stringify(cleanedRecordSections.value);
    const original = JSON.stringify(recordLayoutSectionConfigs.value);
    return current !== original;
  } else if (props.type === "related") {
    const current = JSON.stringify(cleanedRelatedColumns.value);
    const original = JSON.stringify(relatedLayoutColumnConfigs.value);
    return current !== original;
  }
  return false;
});

const form = useForm({
  module_id: props.module.id,
  type: props.type,
  definition: currentLayout.value || {
    [{ list: "columns", record: "sections", related: "columns" }[props.type]]:
      [],
  },
});

const resetToDatabaseValue = () => {
  if (props.type === "list" || props.type === "linkingPanel") {
    listColumns.value = [...selectedListColumnsFromDb.value];
  } else if (props.type === "record") {
    recordSections.value = cloneRecordSectionsFromDb(recordLayoutFromDB.value);
  } else if (props.type === "related") {
    relatedColumns.value = cloneRelatedColumnsFromDb(relatedLayoutFromDB.value);
  }
  form.definition = currentLayout.value || {};
  clearAllAlerts();
  emptyColumns.value = new Set();
  warning(t("layouts.layout_reset_success"));
};

const getEmptyPanels = () => {
  return new Set(
    cleanedRelatedColumns.value
      .map((column, index) => (!column.layout?.length ? index : null))
      .filter((index) => index !== null),
  );
};

const saveLayout = () => {
  info(t("layouts.saving"));
  let definition = { ...(currentLayout.value || {}) };

  if (props.type === "list" || props.type === "linkingPanel") {
    definition.columns = cleanedListColumns.value;
  } else if (props.type === "record") {
    definition.sections = cleanedRecordSections.value;
  } else if (props.type === "related") {
    const empty = getEmptyPanels();
    if (empty.size) {
      emptyColumns.value = empty;
      clearAllAlerts();
      error(t("layouts.has_empty_layouts_error"));
      return;
    }
    emptyColumns.value = new Set();
    definition.columns = cleanedRelatedColumns.value;
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
useUnsavedChangesGuard({
  getIsDirty: () => isDirty.value,
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
      {{ $t("layouts." + type) }} > {{ module.label }} >
      {{ $t("layouts.label") }} > {{ $t("settings.label") }}
    </title>
  </Head>

  <div
    class="settings"
    :style="{
      '--primary-color': appSettings.primary_color,
      '--secondary-color': appSettings.secondary_color,
      '--danger-color': appSettings.danger_color,
      '--module-color': moduleColor,
    }"
  >
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

      <div v-else-if="type === 'record'">
        <LayoutRecordEditor
          v-model:sections="recordSections"
          :available-fields="availableRecordFields"
          :field-by-key="fieldByName"
        />
      </div>
      <div v-else-if="type === 'related'">
        <LayoutRelatedEditor
          v-model:columns="relatedColumns"
          :available-relationships="availableRelationships"
          :rel-by-key="relatedByName"
          :empty-columns="emptyColumns"
        />
      </div>

      <div v-else-if="type === 'subpanels'">
        <LayoutSubpanelEditor
          v-model:columns="listColumns"
          :available-fields="availableListFields"
        />
      </div>

      <div v-else-if="type === 'linkingPanel'">
        <LayoutLinkingPanelEditor
          v-model:columns="listColumns"
          :available-fields="availableListFields"
        />
      </div>

      <div class="settings__actions">
        <button
          @click="resetToDatabaseValue()"
          class="settings__actions__reset"
          type="reset"
          :disabled="!isDirty || form.processing"
        >
          {{ form.processing ? $t("layouts.resetting") : $t("layouts.reset") }}
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
          class="settings__actions__save"
        >
          {{
            form.processing ? $t("layouts.saving") : $t("layouts.save_layout")
          }}
        </button>
      </div>
    </div>
  </div>
</template>
