<script setup>
import Layout from "@/Layouts/Layout.vue";
import {
  computed,
  ref,
  watch,
  getCurrentInstance,
  onMounted,
  nextTick,
} from "vue";
import { Head, usePage, Link, useForm } from "@inertiajs/vue3";
import LayoutListEditor from "@/Pages/Components/Settings/LayoutListEditor.vue";
import LayoutRecordEditor from "@/Pages/Components/Settings/LayoutRecordEditor.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { success, error, info, removeAlert, clearAllAlerts } = useAlerts();
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

const listColumns = ref([]);
const recordSections = ref([]);

const currentLayout = computed(() => {
  const custom = props.module.layouts?.find(
    (layout) => layout.type === props.type
  );
  return custom?.definition || props.defaultLayout?.definition || null;
});

const moduleFields = computed(() => {
  return props.fields ?? [];
});
console.log(moduleFields.value);

const fieldByKey = computed(() => {
  const map = {};
  for (const field of moduleFields.value) {
    if (field?.key) map[field.key] = field;
  }
  return map;
});

const listLayoutColumnConfigs = computed(() => {
  const def = currentLayout.value;
  return def?.columns && Array.isArray(def.columns) ? def.columns : [];
});

const selectedListColumnsFromDb = computed(() => {
  return listLayoutColumnConfigs.value
    .map((col) => {
      const field = fieldByKey.value[col?.key];
      if (!field) return null;

      return {
        ...col,
        field,
        label: col.label ?? field.label ?? col.key,
      };
    })
    .filter(Boolean);
});

const recordLayoutSectionConfigs = computed(() => {
  if (props.type !== "record") return [];
  const def = currentLayout.value;
  return def?.sections && Array.isArray(def.sections) ? def.sections : [];
});

const recordLayoutFromDB = computed(() => {
  if (props.type !== "record") return [];

  return recordLayoutSectionConfigs.value.map((section) => {
    const layout = (section.layout || [])
      .map((col) => {
        const field = fieldByKey.value[col?.key];
        if (!field) return null;

        return {
          ...col,
          field,
          label: col.label ?? field.label ?? col.key,
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
  selectedListColumnsFromDb,
  (val) => {
    listColumns.value = [...val];
  },
  { immediate: true }
);

watch(
  recordLayoutFromDB,
  (val) => {
    recordSections.value = cloneRecordSectionsFromDb(val);
  },
  { immediate: true }
);

const availableFields = computed(() => {
  if (props.type !== "list") return [];

  const usedKeys = new Set(
    listColumns.value.map((col) => col?.key).filter(Boolean)
  );
  return moduleFields.value.filter((field) => !usedKeys.has(field.key));
});

const cleanedListColumns = computed(() =>
  listColumns.value.map((col) => {
    const { field, ...rest } = col || {};
    return rest;
  })
);

const cleanedRecordSections = computed(() =>
  recordSections.value.map((section) => ({
    ...section,
    layout: (section.layout || []).map((col) => {
      const { field, ...rest } = col || {};
      return rest;
    }),
  }))
);

const cleanedColumnsFromDb = computed(() =>
  listLayoutColumnConfigs.value.map((col) => {
    const { field, ...rest } = col || {};
    return rest;
  })
);

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

// Form
const form = useForm({
  module_id: props.module.id,
  type: props.type,
  definition:
    currentLayout.value ||
    (props.type === "list" ? { columns: [] } : { sections: [] }),
});

onMounted(() => {
  // Check if there are any flash messages from the server
  const flash = usePage().props.flash;
  if (flash?.success) {
    success(flash.success);
  } else if (flash?.error) {
    error(flash.error);
  }
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
  const url = `/settings/customisation/layouts/${props.module.id}/${props.type}`;

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
      // Show error alert with the first error message
      const firstError = Object.values(errors)[0];
      error(firstError || "An error occurred while saving the layout.");
    },
  });
};

const availableRecordFields = computed(() => {
  if (props.type !== "record") return [];

  const usedKeys = new Set();
  recordSections.value.forEach((section) => {
    (section.layout || []).forEach((col) => {
      if (col?.key) usedKeys.add(col.key);
    });
  });

  return moduleFields.value.filter((field) => !usedKeys.has(field.key));
});

const manualAlerts = ref([
  {
    id: 1,
    message: "Operation successful",
    type: "success",
    dismissible: true,
  },
  { id: 2, message: "Something went wrong", type: "error", dismissible: true },
]);
</script>

<template>
  <Head>
    <title>
      {{ $t("layouts." + type) }} > {{ module.label }} >
      {{ $t("layouts.label") }} > {{ $t("settings.label") }}
    </title>
  </Head>
  <!-- <Alerts :alerts="alerts"/> -->
  <div class="layout">
    <div class="settings_header">
      <div class="settings_header_title">
        <h5>
          <Link href="/settings">{{ $t("settings.label") }}</Link>
        </h5>
        <span>></span>
        <h5>
          <Link href="/settings/customisation/layouts">{{
            $t("layouts.label")
          }}</Link>
        </h5>
        <span>></span>
        <h5>
          <Link :href="'/settings/customisation/layouts/' + module.id">{{
            module.label
          }}</Link>
        </h5>
        <span>></span>
        <h6>{{ $t("layouts." + type) }}</h6>
      </div>
    </div>

    <div class="layout_editor">
      <div v-if="type === 'list'">
        <LayoutListEditor
          v-model:columns="listColumns"
          :available-fields="availableFields"
        />
      </div>

      <div v-if="type === 'record'">
        <LayoutRecordEditor
          v-model:sections="recordSections"
          :available-fields="availableRecordFields"
          :field-by-key="fieldByKey"
        />
      </div>

      <div class="layout_editor_actions">
        <button
          @click="resetToDatabaseValue()"
          class="reset-btn"
          type="reset"
          :disabled="!isDirty || form.processing"
        >
          {{ form.processing ? $t("layouts.resetting") : $t("layouts.reset") }}
        </button>

        <button
          @click="saveLayout()"
          type="submit"
          :disabled="!isDirty || form.processing"
          class="save-btn"
        >
          {{
            form.processing ? $t("layouts.saving") : $t("layouts.save_layout")
          }}
        </button>
      </div>
    </div>
  </div>
</template>
