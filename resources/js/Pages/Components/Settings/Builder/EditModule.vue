<script setup>
import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import IconPicker from "@/Pages/Components/Settings/Modules/IconPicker.vue";
import ColorPicker from "@/Pages/Components/FiledTypes/ColorPicker.vue";
import { reactive, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import Text from "@/Pages/Components/FiledTypes/Text.vue";
import LongText from "../../FiledTypes/LongText.vue";

const props = defineProps({
  settingModule: Object,
  categoryList: Object,
  color: String,
  errors: Object,
});
const editableModule = reactive({
  display_label: props.settingModule?.label || "",
  single_label: props.settingModule?.single_label || "",
  ...props.settingModule,
});
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
    "handler_class",
    "label",
    "is_draft",
    "locked_by",
    "locked_until",
  ];
  return Object.entries(editableModule).filter(
    ([key]) => !ignore.includes(key),
  );
});

const slug = computed(() => {
  const label = editableModule?.display_label || "";
  if (label === "") return;
  return label
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

const inputTypeFor = (key, value) => {
  if (key === "show_in_sidebar") return "checkbox";
  if (key === "display_label") return "display_label";
  if (key === "slug") return "slug";
  if (key === "icon") return "icon";
  if (typeof value === "number") return "number";
  if (key === "color") return "color";
  if (key === "category") return "select";
  if (key === "description") return "textarea";
  return "text";
};
const emit = defineEmits([
  "dirty",
  "update",
  "missing-fields",
  "is-form-dirty",
]);
const requiredFields = ["display_label", "single_label", "category"];
// 1. Validation: Checks if required fields are empty
const hasMissingRequired = computed(() => {
  return requiredFields.some((field) => {
    const value = editableModule[field];
    return value === null || value === undefined || String(value).trim() === "";
  });
});

// 2. Dirtiness: Checks if the user actually changed data
const formDirty = computed(() => {
  return editableFields.value.some(([key, value]) => {
    // Special handling for labels to avoid empty string vs undefined issues
    if (key === "display_label") {
      const originalLabel = props.settingModule?.label || "";
      return String(value).trim() !== String(originalLabel).trim();
    }
    if (key === "single_label") {
      const originalSingle = props.settingModule?.single_label || "";
      return String(value).trim() !== String(originalSingle).trim();
    }

    const original = props.settingModule[key];
    const current = editableModule[key];

    if (typeof original === "number" && typeof current === "boolean") {
      return Boolean(original) !== current;
    }

    return original !== current;
  });
});

// Watchers (Keep your existing watchers that emit these)
watch(hasMissingRequired, (val) => emit("missing-fields", val), {
  immediate: true,
});
watch(formDirty, (val) => emit("is-form-dirty", val), { immediate: true });

watch(
  slug,
  (newSlug) => {
    editableModule.slug = newSlug;
  },
  { immediate: true },
);

watch(
  () => editableModule,
  (newValue) => {
    emit("update", { ...newValue });
  },
  { deep: true, immediate: true },
);
</script>

<template>
  <div>
    <form @submit.prevent="submitHandler">
      <div
        v-for="[key, value] in editableFields"
        :key="key"
        class="settings__module__edit__element"
      >
        <label class="settings__module__edit__element__label">
          {{ $t("settings.modules." + key) }}
        </label>

        <div class="settings__module__edit__element__content">
          <Checkbox
            v-if="inputTypeFor(key, value) === 'checkbox'"
            v-model="editableModule[key]"
            :module-color="color"
          />

          <IconPicker
            v-else-if="inputTypeFor(key, value) === 'icon'"
            v-model="editableModule[key]"
            :color="color"
          />

          <Text
            v-else-if="inputTypeFor(key, value) === 'slug'"
            type="text"
            :read-only="true"
            :model-value="slug"
            mode="settings"
            :has-error="errors[key]"
          />

          <LongText
            v-else-if="inputTypeFor(key, value) === 'textarea'"
            v-model="editableModule[key]"
            mode="settings"
          ></LongText>

          <ColorPicker
            v-else-if="inputTypeFor(key, value) === 'color'"
            v-model="editableModule[key]"
          />

          <Select
            v-else-if="inputTypeFor(key, value) === 'select'"
            :dropdown_list="categoryList"
            v-model="editableModule[key]"
            mode="module-builder"
          />

          <Text
            v-else
            type="text"
            v-model="editableModule[key]"
            mode="settings"
            :has-error="errors[key]"
          />
        </div>
      </div>
    </form>
  </div>
</template>
