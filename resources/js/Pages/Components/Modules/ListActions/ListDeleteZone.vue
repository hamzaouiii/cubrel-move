<script setup>
import { computed } from "vue";

const props = defineProps({
  selectedIds: { type: Array, default: () => [] },
  excludedIds: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  allMatchingSelected: { type: Boolean, default: false },
});

const emit = defineEmits([
  "selectAllMatching", // replaces "toggleAll"
  "clearSelection",
  "cancelClicked",
  "deleteClicked",
]);

const emitSelectAllMatching = () => emit("selectAllMatching");
const emitClearSelection = () => emit("clearSelection");
const emitCancelClicked = () => emit("cancelClicked");
const emitDeleteClicked = () => emit("deleteClicked");

// ─── Selection counts ────────────────────────────────────────────────────────

const totalSelected = computed(() => {
  if (props.allMatchingSelected) {
    return (props.meta?.total ?? 0) - props.excludedIds.length;
  }
  return props.selectedIds.length;
});

/**
 * Show the "Select all N records" prompt when:
 * - NOT in allMatching mode yet
 * - At least one record is selected
 * - Result set is larger than current page selection
 */
const showSelectAllPrompt = computed(() => {
  return (
    !props.allMatchingSelected &&
    props.selectedIds.length > 0 &&
    (props.meta?.total ?? 0) > props.selectedIds.length
  );
});
</script>

<template>
  <div class="delete-zone">
    <div class="delete-zone__text">
      <!-- Zero selection state -->
      <div v-if="!totalSelected">
        <span>{{ $t("modules.delete.description") }}</span>
      </div>

      <!-- Selected count -->
      <div v-else>
        <span>
          {{ $t("modules.delete.selected_count", { count: totalSelected }) }}
        </span>
      </div>

      <!-- "Select all N records in result set" prompt -->
      <span
        v-if="showSelectAllPrompt"
        class="select-all-in-scope"
        @click="emitSelectAllMatching"
      >
        {{ $t("modules.delete.select_all", { total: meta.total }) }}
      </span>
      <span v-else> </span>
    </div>

    <div class="list__actions">
      <button
        :disabled="!totalSelected"
        class="list__actions--secondary"
        @click="emitClearSelection"
      >
        {{ $t("modules.delete.clear_selection") }}
      </button>
      <button class="list__actions--secondary" @click="emitCancelClicked">
        {{ $t("modules.actions.cancel") }}
      </button>
      <button
        class="list__actions--danger"
        :disabled="!totalSelected"
        @click="emitDeleteClicked"
      >
        {{ $t("modules.delete.delete") }}
      </button>
    </div>
  </div>
</template>
