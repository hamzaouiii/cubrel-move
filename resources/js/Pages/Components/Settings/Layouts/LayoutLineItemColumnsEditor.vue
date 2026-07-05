<script setup>
import { ref, watch, computed, getCurrentInstance, onBeforeUnmount } from "vue";

const props = defineProps({
  columns: {
    type: Array,
    default: () => [],
  },
  availableFields: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:columns"]);

const internalColumns = ref([...props.columns]);

watch(
  () => props.columns,
  (val) => {
    internalColumns.value = [...val];
  },
  { deep: true },
);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const query = ref("");
const searchFocused = ref(false);
const searchInput = ref(null);

const onSearchBlur = () => {
  // Delayed so a mousedown on a result (which blurs the input first) still
  // registers as a click before the results list disappears.
  setTimeout(() => {
    searchFocused.value = false;
  }, 150);
};

const openResults = () => {
  searchFocused.value = true;
};

const closeResults = () => {
  searchFocused.value = false;
  searchInput.value?.blur();
};

const toggleResults = () => {
  if (searchFocused.value) {
    closeResults();
  } else {
    searchInput.value?.focus();
    openResults();
  }
};

const filteredAvailable = computed(() => {
  const q = query.value.trim().toLowerCase();
  if (!q) return props.availableFields;
  return props.availableFields.filter((field) => {
    const label = (t(field.label) ?? field.name ?? "").toLowerCase();
    return label.includes(q) || (field.name ?? "").toLowerCase().includes(q);
  });
});

const addField = (field) => {
  internalColumns.value = [...internalColumns.value, { ...field }];
  query.value = "";
  searchFocused.value = false;
  emitUpdatedColumns();
};

const removeColumn = (index) => {
  const columns = [...internalColumns.value];
  columns.splice(index, 1);
  internalColumns.value = columns;
  emitUpdatedColumns();
};

// Drag-and-drop below mirrors LayoutListEditor.vue's ghost/drop-zone mechanics
// (same lerp-animated floating ghost, same before/after drop-zone strips),
// trimmed to a single reorderable list since there's no more sidebar to drag from.
const dragging = ref(null);
const originOffset = ref({ x: 0, y: 0 });
const dragOver = ref(null);
const ghostWidth = ref(null);
const ghostHeight = ref(null);
const dragPosition = ref({ x: 0, y: 0 });
const ghostRenderPos = ref({ x: 0, y: 0 });

let ghostAnimationFrame = null;

const transparentPixel = "data:image/gif;base64,R0lGODlhAQABAAAAACw=";
const dragImage = new Image();
dragImage.src = transparentPixel;

const ghostLabel = computed(() => {
  if (dragging.value === null) return "";
  const item = internalColumns.value[dragging.value];
  return item ? (t(item.label) ?? item.name) : "";
});

const startDrag = (index, event) => {
  dragging.value = index;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = "move";
    event.dataTransfer.setData("text/plain", String(index));
    try {
      event.dataTransfer.setDragImage(dragImage, 0, 0);
    } catch (e) {}
  }
  const el = event.target.closest(".lic-editor__columns__item");
  if (el) {
    ghostWidth.value = el.offsetWidth + "px";
    ghostHeight.value = el.offsetHeight + "px";
    const rect = el.getBoundingClientRect();
    originOffset.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };
    ghostRenderPos.value = { x: event.clientX, y: event.clientY };
  } else {
    ghostWidth.value = null;
    ghostHeight.value = null;
  }
  startGhostAnimation();
};

const endDrag = () => {
  dragging.value = null;
  dragOver.value = null;
};

const setDragOver = (index, event) => {
  event.preventDefault();
  dragOver.value = index;
};

const isItemDragging = (index) => dragging.value === index;
const isDropZoneActive = (index) => dragOver.value === index;

const moveWithinList = (fromIndex, toIndex) => {
  if (fromIndex === toIndex) return;
  const list = [...internalColumns.value];
  const [item] = list.splice(fromIndex, 1);
  list.splice(toIndex, 0, item);
  internalColumns.value = list;
};

const onDropOnColumns = (targetIndex, event) => {
  event.preventDefault();
  if (dragging.value === null) return;

  moveWithinList(dragging.value, targetIndex);
  emitUpdatedColumns();
  endDrag();
};

const onGlobalDragOver = (event) => {
  if (dragging.value === null) return;
  dragPosition.value = { x: event.clientX, y: event.clientY };
};

const stepGhost = () => {
  const lerp = 0.2;
  const { x: tx, y: ty } = dragPosition.value;
  const { x, y } = ghostRenderPos.value;

  ghostRenderPos.value = {
    x: x + (tx - x) * lerp,
    y: y + (ty - y) * lerp,
  };

  ghostAnimationFrame = requestAnimationFrame(stepGhost);
};

const startGhostAnimation = () => {
  if (ghostAnimationFrame !== null) return;
  ghostAnimationFrame = requestAnimationFrame(stepGhost);
};

const stopGhostAnimation = () => {
  if (ghostAnimationFrame !== null) {
    cancelAnimationFrame(ghostAnimationFrame);
    ghostAnimationFrame = null;
  }
};

onBeforeUnmount(() => {
  stopGhostAnimation();
});

const emitUpdatedColumns = () => {
  const clean = internalColumns.value.map((col) => {
    const { field, ...rest } = col;
    return rest;
  });
  emit("update:columns", clean);
};
</script>

<template>
  <div class="lic-editor" @dragover="onGlobalDragOver">
    <div class="lic-editor__toolbar">
      <div class="lic-editor__search">
        <i
          class="fa-solid fa-magnifying-glass lic-editor__search__icon"
          @mousedown.prevent="toggleResults"
        ></i>
        <input
          ref="searchInput"
          v-model="query"
          type="text"
          class="lic-editor__search__input"
          :placeholder="$t('layouts.line_items_columns_search')"
          @focus="openResults"
          @click="openResults"
          @keydown.esc="closeResults"
          @blur="onSearchBlur"
        />
        <button
          v-if="searchFocused"
          type="button"
          class="lic-editor__search__close"
          :title="$t('layouts.close')"
          @mousedown.prevent="closeResults"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div v-if="searchFocused" class="lic-editor__results">
        <button
          v-for="field in filteredAvailable"
          :key="field.name"
          type="button"
          class="lic-editor__results__item"
          @mousedown.prevent="addField(field)"
        >
          <span>{{ $t(field.label) ?? field.name }}</span>
          <i class="fa-solid fa-plus"></i>
        </button>
        <p v-if="!filteredAvailable.length" class="lic-editor__results__empty">
          {{
            query.trim()
              ? $t("layouts.no_matching_fields")
              : $t("layouts.all_fields_used")
          }}
        </p>
      </div>
    </div>

    <div class="lic-editor__columns">
      <div
        class="lic-editor__columns__drop-zone"
        :class="{
          'lic-editor__columns__drop-zone--active': isDropZoneActive(0),
        }"
        @dragover="setDragOver(0, $event)"
        @drop="onDropOnColumns(0, $event)"
      />

      <template v-for="(col, index) in internalColumns" :key="col.name">
        <div
          class="lic-editor__columns__item"
          :class="{
            'lic-editor__columns__item--dragging': isItemDragging(index),
          }"
        >
          <div
            class="lic-editor__columns__item__content"
            draggable="true"
            @dragstart="startDrag(index, $event)"
            @dragend="endDrag"
          >
            <span class="lic-editor__columns__item__handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </span>
            <span class="lic-editor__columns__item__label">
              {{ $t(col.label) ?? col.name }}
            </span>
            <button
              class="lic-editor__columns__item__remove"
              type="button"
              :title="$t('layouts.remove_column')"
              @click="removeColumn(index)"
            >
              <i class="fa-solid fa-times"></i>
            </button>
          </div>

          <div
            class="lic-editor__columns__drop-zone"
            :class="{
              'lic-editor__columns__drop-zone--active': isDropZoneActive(
                index + 1,
              ),
            }"
            @dragover="setDragOver(index + 1, $event)"
            @drop="onDropOnColumns(index + 1, $event)"
          />
        </div>
      </template>

      <div v-if="internalColumns.length === 0" class="lic-editor__empty">
        {{ $t("layouts.line_items_columns_empty") }}
      </div>
    </div>

    <div
      v-if="dragging !== null"
      class="lic-editor__ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
        width: ghostWidth || 'auto',
        height: ghostHeight || 'auto',
      }"
    >
      <span class="lic-editor__ghost__handle">
        <i class="fa-solid fa-grip-vertical"></i>
      </span>
      <span class="lic-editor__ghost__label">
        {{ ghostLabel }}
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.lic-editor {
  position: relative;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: none;
  padding: 10px;
  user-select: none;

  &__toolbar {
    position: relative;
    margin-bottom: 8px;
  }

  &__search {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f9fafb;
    transition: border-color 0.15s ease;

    &:focus-within {
      border-color: var(--module-color, #3498db);
    }

    &__icon {
      color: #9ca3af;
      font-size: 0.78rem;
      cursor: pointer;

      &:hover {
        color: var(--module-color, #3498db);
      }
    }

    &__close {
      display: flex;
      align-items: center;
      justify-content: center;
      background: none;
      border: none;
      color: #9ca3af;
      cursor: pointer;
      padding: 2px 4px;
      border-radius: 4px;
      font-size: 0.75rem;

      &:hover {
        color: #374151;
        background: rgba(0, 0, 0, 0.05);
      }
    }

    &__input {
      flex: 1;
      border: none;
      background: transparent;
      font-size: 0.85rem;
      color: #374151;

      &:focus {
        outline: none;
      }

      &::placeholder {
        color: #9ca3af;
      }
    }
  }

  &__results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 5;
    max-height: 190px;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
    padding: 4px;

    &__item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 6px 8px;
      border: none;
      background: transparent;
      border-radius: 4px;
      font-size: 0.82rem;
      color: #374151;
      cursor: pointer;
      text-align: left;

      i {
        color: var(--module-color, #3498db);
        font-size: 0.7rem;
      }

      &:hover {
        background: color-mix(in srgb, var(--module-color, #3498db) 10%, white);
      }
    }

    &__empty {
      padding: 6px 8px;
      font-size: 0.8rem;
      color: #9ca3af;
      margin: 0;
    }
  }

  &__columns {
    position: relative;

    &__drop-zone {
      height: 4px;
      margin: 2px 0;
      background: transparent;
      border-radius: 2px;
      transition: all 0.2s;

      &--active {
        background: rgba(80, 161, 255, 0.06);
        border: 1px dashed rgba(0, 0, 0, 0.4);
        height: 34px;
        margin: 6px 0;
      }
    }

    &__item {
      position: relative;

      &--dragging &__content {
        opacity: 0.4;
      }

      &__content {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 8px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #f9fafb;
        cursor: move;
        transition: all 0.15s ease;

        &:hover {
          border-color: #adb5bd;
        }
      }

      &__handle {
        color: #adb5bd;
        font-size: 0.78rem;

        &:active {
          cursor: grabbing;
        }
      }

      &__label {
        flex: 1;
        font-size: 0.85rem;
        color: #212529;
      }

      &__remove {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 0.8rem;

        &:hover {
          color: #dc3545;
          background: rgba(220, 53, 69, 0.1);
        }
      }
    }
  }

  &__empty {
    padding: 14px;
    text-align: center;
    color: #9ca3af;
    font-size: 0.82rem;
    border: 1px dashed #dee2e6;
    border-radius: 6px;
  }

  &__ghost {
    position: fixed;
    z-index: 100;
    pointer-events: none;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 6px 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0.9;
    transform: translateZ(0);

    &__handle {
      color: #adb5bd;
    }

    &__label {
      font-size: 0.85rem;
      color: #212529;
    }
  }
}
</style>
