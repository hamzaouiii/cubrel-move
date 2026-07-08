<script setup>
import { computed, ref, getCurrentInstance } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";
import { useAlerts } from "@/Composables/useAlerts";

const { error } = useAlerts();
const { proxy } = getCurrentInstance();
const t = proxy.$t;

const props = defineProps({
  selectedIds: { type: Array, default: () => [] },
  excludedIds: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  allMatchingSelected: { type: Boolean, default: false },
  fields: { type: Object, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
  "selectAllMatching",
  "clearSelection",
  "massUpdate",
  "cancelClicked",
]);

const emitSelectAllMatching = () => emit("selectAllMatching");
const emitClearSelection = () => emit("clearSelection");
const emitCancelClicked = () => emit("cancelClicked");

const form = useForm({
  field: null,
  inputValue: null,
});

const totalSelected = computed(() => {
  if (props.allMatchingSelected) {
    return (props.meta?.total ?? 0) - props.excludedIds.length;
  }
  return props.selectedIds.length;
});

const showSelectAllPrompt = computed(() => {
  return (
    !props.allMatchingSelected &&
    props.selectedIds.length > 0 &&
    props.meta?.total > props.selectedIds.length
  );
});

const fieldDropDownOptions = computed(() => {
  return (props.fields ?? [])
    .filter((e) => !e.readonly)
    .map((item) => ({
      value: item.key,
      label: item.label,
    }));
});

const related_field = computed(() => ({ values: fieldDropDownOptions.value }));

const moduleFieldsField = computed(() => ({
  id: "1",
  is_draft: false,
  key: "defaults_fields_field",
  label: "modules.defaults.fields_field",
  module_id: null,
  name: "fields_field",
  readonly: true,
  required: false,
  searchable: true,
  nullable: true,
  sortable: true,
  type: "select",
  dropdown_list: related_field.value,
}));

const getField = (key) => props.fields?.find((field) => field.key === key);

const selectedField = computed(() => getField(form.field));

const isEmptyValue = (value) =>
  value === "" ||
  value === null ||
  value === undefined ||
  (Array.isArray(value) && value.length === 0);

const requiredValueMissing = computed(() => {
  return (
    Boolean(selectedField.value?.required) && isEmptyValue(form.inputValue)
  );
});

const attemptedSubmit = ref(false);
const showRequiredError = computed(
  () => attemptedSubmit.value && requiredValueMissing.value,
);

const canSubmit = computed(() => {
  return Boolean(totalSelected.value) && Boolean(form.field);
});

const emitMassUpdate = () => {
  if (!canSubmit.value) return;

  attemptedSubmit.value = true;
  if (requiredValueMissing.value) {
    error(
      `${t(selectedField.value.label)} ${t("fields.validation.is_required")}`,
      {
        timeout: 1000,
      },
    );
    return;
  }

  emit("massUpdate", {
    allMatchingSelected: props.allMatchingSelected,
    selectedIds: props.selectedIds,
    excludedIds: props.excludedIds,
    filters: props.filters ?? {},
    field: form.field,
    value: form.inputValue,
  });
};

const resetInputValue = () => {
  form.inputValue = null;
  attemptedSubmit.value = false;
  inputValueLabel.value = null;
};

const fieldOverlayOpen = ref(false);
const inputValueLabel = ref(null);

const allModules = computed(() => usePage().props.modules);
const getIcon = (slug) => {
  if (!slug) return null;
  return (
    allModules.value?.find((m) => m.slug === slug)?.icon || "fa-solid fa-user"
  );
};

const openFieldOverlay = () => {
  if (!selectedField.value?.related_module) return;
  fieldOverlayOpen.value = true;
};

const onFieldRecordSelect = (record) => {
  form.inputValue = record.id;
  inputValueLabel.value = record.name;
  fieldOverlayOpen.value = false;
};
</script>

<template>
  <div class="mass-update-zone">
    <div class="mass-update-zone__content">
      <div class="mass-update-zone__text">
        <div v-if="!totalSelected">
          <span>{{ $t("modules.update.description") }}</span>
        </div>

        <div v-else>
          <span>
            {{ $t("modules.update.selected_count", { count: totalSelected }) }}
          </span>
        </div>

        <span
          v-if="showSelectAllPrompt"
          class="select-all-in-scope"
          @click="emitSelectAllMatching"
        >
          {{ $t("modules.update.select_all", { total: meta.total }) }}
        </span>
      </div>
    </div>

    <div class="list__actions">
      <button
        :disabled="!totalSelected"
        class="list__actions--secondary"
        @click="emitClearSelection"
      >
        {{ $t("modules.update.clear_selection") }}
      </button>
      <button class="list__actions--secondary" @click="emitCancelClicked">
        {{ $t("modules.update.cancel") }}
      </button>
      <button
        class="list__actions--primary"
        :disabled="!canSubmit"
        @click="emitMassUpdate"
      >
        {{ $t("modules.update.update") }}
      </button>
    </div>

    <div class="mass-update-zone__inputs">
      <div class="definition">
        <FieldRenderer
          :field="moduleFieldsField"
          v-model="form.field"
          mode="edit"
          placeholder="Select Field"
          @change="resetInputValue()"
        />
      </div>
      <div class="value">
        <FieldRenderer
          v-if="form.field"
          :field="getField(form.field)"
          v-model="form.inputValue"
          mode="edit"
          :has-error="showRequiredError"
          :related_label="inputValueLabel"
          :icon="getIcon(selectedField?.related_module)"
          @open-link-overlay="openFieldOverlay"
        />
      </div>
    </div>

    <RecordSelectorDrawer
      :open="fieldOverlayOpen"
      :search-endpoint="
        selectedField?.related_module
          ? `/relatedfield/search/${selectedField.related_module}`
          : ''
      "
      :related-module="selectedField?.related_module"
      :icon="getIcon(selectedField?.related_module)"
      :selected-record="form.inputValue"
      :fields="fields"
      @select="onFieldRecordSelect"
      @close="fieldOverlayOpen = false"
    />
  </div>
</template>
