<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import DropdownField from "./Settings/FiledTypes/DropdownField.vue";

const props = defineProps({
  selectedIds: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  allMatchingSelected: { type: Boolean, default: false },
  fields: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});
const emit = defineEmits([
  "toggleAll",
  "clearSelection",
  "massUpdate",
  "cancelClicked",
]);

const emitToggleAll = () => emit("toggleAll");
const emitClearSelection = () => emit("clearSelection");
const emitCancelClicked = () => emit("cancelClicked");

const form = useForm({
  field: null,
  inputValue: null,
});

const fieldDropDownOptions = computed(() => {
  return (props.fields ?? []).map((item) => ({
    value: item.key,
    label: item.label,
  }));
});

const totalSelected = computed(() => {
  if (props.allMatchingSelected) return props.meta?.total ?? 0;
  return props.selectedIds.length;
});

const showSelectAll = computed(() => totalSelected.value > 0);

const canSubmit = computed(() => {
  return (
    Boolean(totalSelected.value) &&
    Boolean(form.field) &&
    String(form.inputValue ?? "").trim().length > 0
  );
});

const emitMassUpdate = () => {
  if (!canSubmit.value) return;

  emit("massUpdate", {
    allMatchingSelected: props.allMatchingSelected,
    selectedIds: props.selectedIds,
    filters: props.filters ?? {},
    field_key: form.field,
    new_value: form.inputValue,
  });
};
</script>

<template>
  <div class="mass-update-zone">
    <div class="mass-update-form">
      <DropdownField v-model="form.field" :options="fieldDropDownOptions" />
      <input v-model="form.inputValue" />

      <button :disabled="!totalSelected" @click="emitClearSelection">
        {{ $t("modules.update.clear_selection") }}
      </button>
      <button @click="emitCancelClicked">
        {{ $t("modules.update.cancel") }}
      </button>

      <button
        class="update_confirm"
        :disabled="!canSubmit"
        @click="emitMassUpdate"
      >
        {{ $t("modules.update.update") }}
      </button>
    </div>

    <div class="mass-update-summary">
      <div v-if="!totalSelected">
        <span>{{ $t("modules.update.description") }}</span>
      </div>

      <div v-else :class="{ 'hide-this': !totalSelected }">
        <span>
          {{ $t("modules.update.selected_count", { count: totalSelected }) }}
        </span>
      </div>

      <span
        v-if="!allMatchingSelected"
        :class="['select-all-in-scope', { 'hide-this': !showSelectAll }]"
        @click="emitToggleAll"
      >
        {{ $t("modules.update.select_all", { total: meta.total }) }}
      </span>
    </div>
  </div>
</template>
