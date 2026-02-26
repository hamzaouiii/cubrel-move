<script setup>
import { computed, ref } from "vue";
const props = defineProps({
  selectedIds: Array,
  meta: Object,
  allMatchingSelected: Boolean,
});

const emit = defineEmits([
  "toggleAll",
  "clearSelection",
  "cancelClicked",
  "deleteClicked",
]);

const emitToggleAll = () => {
  emit("toggleAll");
};
const emitClearSelection = () => {
  emit("clearSelection");
};

const emitCancelClicked = () => {
  emit("cancelClicked");
};
const emitDeleteClicked = () => {
  emit("deleteClicked");
};

const showSelectAll = computed(() => {
  if (totalSelected.value > 0) {
    return true;
  }
  return false;
});
const totalSelected = computed(() => {
  if (props.allMatchingSelected) {
    return props.meta?.total ?? 0;
  }

  return props.selectedIds.length;
});
</script>

<template>
  <div class="delete-zone">
    <div class="delete-zone__text">
      <div v-if="!totalSelected">
        <span>{{ $t("modules.delete.description") }}</span>
      </div>

      <div v-else :class="{ 'selected-count': !totalSelected }">
        <span>
          {{ $t("modules.delete.selected_count", { count: totalSelected }) }}
        </span>
      </div>
      <span
        v-if="!allMatchingSelected"
        :class="['select-all-in-scope', { 'selected-count': !showSelectAll }]"
        @click="emitToggleAll()"
      >
        {{ $t("modules.delete.select_all", { total: meta.total }) }}
      </span>
      <span v-else> </span>
    </div>
    <div class="delete-zone__actions">
      <button
        :disabled="!totalSelected"
        class="btn delete-zone__actions__cancel"
        @click="emitClearSelection()"
      >
        {{ $t("modules.delete.clear_selection") }}
      </button>
      <button
        class="delete-zone__actions__cancel btn"
        @click="emitCancelClicked"
      >
        {{ $t("modules.actions.cancel") }}
      </button>
      <button
        class="delete-zone__actions__delete btn"
        @click="emitDeleteClicked"
        :disabled="!totalSelected"
      >
        {{ $t("modules.delete.delete") }}
      </button>
    </div>
  </div>
</template>
