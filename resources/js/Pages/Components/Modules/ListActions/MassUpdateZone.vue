<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";

const props = defineProps({
  selectedIds: { type: Array, default: () => [] },
  excludedIds: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  allMatchingSelected: { type: Boolean, default: false },
  fields: { type: Object, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
  "selectAllMatching", // replaces "toggleAll" — fires when user clicks the prompt
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

// ─── Selection counts ────────────────────────────────────────────────────────

const totalSelected = computed(() => {
  if (props.allMatchingSelected) {
    return (props.meta?.total ?? 0) - props.excludedIds.length;
  }
  return props.selectedIds.length;
});

/**
 * Show the "Select all N records" prompt when:
 * - NOT already in allMatching mode
 * - At least one record on the current page is selected
 * - There are more records in the result set than are visible on this page
 */
const showSelectAllPrompt = computed(() => {
  return (
    !props.allMatchingSelected &&
    props.selectedIds.length > 0 &&
    props.meta?.total > props.selectedIds.length
  );
});

// ─── Field dropdown ──────────────────────────────────────────────────────────

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

// ─── Submit guard ─────────────────────────────────────────────────────────────

const canSubmit = computed(() => {
  return (
    Boolean(totalSelected.value) &&
    Boolean(form.field) &&
    String(form.inputValue ?? "").trim().length > 0
  );
});

// ─── Emit mass update ────────────────────────────────────────────────────────

const emitMassUpdate = () => {
  if (!canSubmit.value) return;

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
};
</script>

<template>
  <div class="mass-update-zone">
    <div class="mass-update-zone__content">
      <div class="mass-update-zone__text">
        <!-- Zero selection state -->
        <div v-if="!totalSelected">
          <span>{{ $t("modules.update.description") }}</span>
        </div>

        <!-- Selected count -->
        <div v-else>
          <span>
            {{ $t("modules.update.selected_count", { count: totalSelected }) }}
          </span>
        </div>

        <!-- "Select all N records in result set" prompt -->
        <span
          v-if="showSelectAllPrompt"
          class="select-all-in-scope"
          @click="emitSelectAllMatching"
        >
          {{ $t("modules.update.select_all", { total: meta.total }) }}
        </span>
        <span v-else> </span>
      </div>
    </div>

    <div class="mass-update-zone__actions">
      <button
        :disabled="!totalSelected"
        class="mass-update-zone__actions__cancel"
        @click="emitClearSelection"
      >
        {{ $t("modules.update.clear_selection") }}
      </button>
      <button
        class="mass-update-zone__actions__cancel"
        @click="emitCancelClicked"
      >
        {{ $t("modules.update.cancel") }}
      </button>
      <button
        class="mass-update-zone__actions__update"
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
        />
      </div>
    </div>
  </div>
</template>
