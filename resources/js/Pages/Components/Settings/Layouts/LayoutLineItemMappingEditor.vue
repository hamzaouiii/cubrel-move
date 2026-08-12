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

  sourceFields: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:columns"]);

const internalColumns = ref([...props.columns]);
const internalAvailable = ref([...props.availableFields]);

watch(
  () => props.columns,
  (val) => {
    internalColumns.value = [...val];
  },
  { deep: true },
);

watch(
  () => props.availableFields,
  (val) => {
    internalAvailable.value = [...val];
  },
  { deep: true },
);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

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
  if (!dragging.value) return "";
  const { list, index } = dragging.value;
  const item =
    list === "available"
      ? internalAvailable.value[index]
      : internalColumns.value[index];
  return item ? (t(item.label) ?? item.name) : "";
});

const startDrag = (listName, index, event) => {
  dragging.value = { list: listName, index };
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = "move";
    event.dataTransfer.setData("text/plain", `${listName}:${index}`);
    try {
      event.dataTransfer.setDragImage(dragImage, 0, 0);
    } catch (e) {}
  }
  const el = event.target.closest(".llime-item");
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

const setDragOver = (listName, index, event) => {
  event.preventDefault();
  dragOver.value = { list: listName, index };
};

const isItemDragging = (listName, index) =>
  dragging.value?.list === listName && dragging.value?.index === index;

const isDropZoneActive = (listName, index) =>
  dragOver.value?.list === listName && dragOver.value?.index === index;

const moveWithinList = (listRef, fromIndex, toIndex) => {
  if (fromIndex === toIndex) return;
  const list = [...listRef.value];
  const [item] = list.splice(fromIndex, 1);
  list.splice(toIndex, 0, item);
  listRef.value = list;
};

const moveBetweenLists = (fromRef, toRef, fromIndex, toIndex) => {
  const from = [...fromRef.value];
  const to = [...toRef.value];
  const [item] = from.splice(fromIndex, 1);

  to.splice(toIndex, 0, { source_field: null, ...item });
  fromRef.value = from;
  toRef.value = to;
};

const onDropOnColumns = (targetIndex, event) => {
  event.preventDefault();
  if (!dragging.value) return;
  const { list, index } = dragging.value;

  if (list === "columns") {
    moveWithinList(internalColumns, index, targetIndex);
  } else if (list === "available") {
    moveBetweenLists(internalAvailable, internalColumns, index, targetIndex);
  }

  emitUpdatedColumns();
  endDrag();
};

const onDropOnAvailable = (targetIndex, event) => {
  event.preventDefault();
  if (!dragging.value) return;
  const { list, index } = dragging.value;

  if (list === "available") {
    moveWithinList(internalAvailable, index, targetIndex);
  } else if (list === "columns") {
    moveBetweenLists(internalColumns, internalAvailable, index, targetIndex);
    emitUpdatedColumns();
  }

  endDrag();
};

const removeColumn = (index) => {
  const columns = [...internalColumns.value];
  columns.splice(index, 1);
  internalColumns.value = columns;
  emitUpdatedColumns();
};

const updateSourceField = (index, value) => {
  const columns = [...internalColumns.value];
  columns[index] = { ...columns[index], source_field: value || null };
  internalColumns.value = columns;
  emitUpdatedColumns();
};

const emitUpdatedColumns = () => {
  const clean = internalColumns.value.map((col) => {
    const { field, ...rest } = col;
    return rest;
  });
  emit("update:columns", clean);
};

const onGlobalDragOver = (event) => {
  if (!dragging.value) return;
  dragPosition.value = { x: event.clientX, y: event.clientY };
};

const stepGhost = () => {
  const lerp = 0.2;
  const { x: tx, y: ty } = dragPosition.value;
  const { x, y } = ghostRenderPos.value;
  ghostRenderPos.value = { x: x + (tx - x) * lerp, y: y + (ty - y) * lerp };
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

onBeforeUnmount(() => stopGhostAnimation());
</script>

<template>
  <div class="editor" @dragover="onGlobalDragOver">
    <div class="editor__container">
      <div class="editor__container__sidebar">
        <div class="editor__container__sidebar__header">
          <span class="editor__container__sidebar__header__title">{{
            $t("layouts.available_fields")
          }}</span>
          <small>{{ $t("layouts.drag_to_sections") }}</small>
        </div>

        <ul class="editor__available-fields">
          <div
            class="editor__empty-drop-zone"
            :class="{
              'editor__empty-drop-zone--active': isDropZoneActive(
                'available',
                0,
              ),
            }"
            @dragover="setDragOver('available', 0, $event)"
            @drop="onDropOnAvailable(0, $event)"
          >
            {{ $t("layouts.drop_here_to_remove") }}
          </div>

          <template
            v-for="(field, index) in internalAvailable"
            :key="field.name"
          >
            <li
              class="editor__available-fields__item llime-item"
              :class="{
                'editor__available-fields__item--dragging': isItemDragging(
                  'available',
                  index,
                ),
              }"
              draggable="true"
              @dragstart="startDrag('available', index, $event)"
              @dragend="endDrag"
            >
              <span class="editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span class="editor__available-fields__item__label">
                {{ $t(field.label) ?? field.name }}
              </span>
            </li>

            <li
              class="lle-drop-zone"
              :class="{
                'lle-drop-zone--active': isDropZoneActive(
                  'available',
                  index + 1,
                ),
              }"
              @dragover="setDragOver('available', index + 1, $event)"
              @drop="onDropOnAvailable(index + 1, $event)"
            />
          </template>
          <div v-if="internalAvailable.length === 0" class="rle-no-fields">
            {{ $t("layouts.all_fields_used") }}
          </div>
        </ul>
      </div>

      <div class="editor__container__main">
        <div class="editor__sections">
          <div class="editor__sections__header">
            <span class="editor__sections__header__title">
              {{ $t("layouts.line_items_mapping_fields") }}
            </span>
            <small>{{ $t("layouts.line_items_mapping_fields_hint") }}</small>
          </div>
          <div class="editor__sections__item">
            <div class="editor__sections__item__content">
              <div class="editor__columns editor__columns--vertical">
                <div
                  class="editor__columns__drop-zone"
                  :class="{
                    'editor__columns__drop-zone--active': isDropZoneActive(
                      'columns',
                      0,
                    ),
                  }"
                  @dragover="setDragOver('columns', 0, $event)"
                  @drop="onDropOnColumns(0, $event)"
                />

                <template
                  v-for="(col, index) in internalColumns"
                  :key="col.name"
                >
                  <div
                    class="editor__columns__item llime-item"
                    :class="{
                      'editor__columns__item--dragging': isItemDragging(
                        'columns',
                        index,
                      ),
                    }"
                  >
                    <div
                      class="editor__columns__item__content"
                      draggable="true"
                      @dragstart="startDrag('columns', index, $event)"
                      @dragend="endDrag"
                    >
                      <span class="editor__columns__item__handle">
                        <i class="fa-solid fa-grip-vertical"></i>
                      </span>
                      <span class="editor__columns__item__label">
                        {{ $t(col.label) ?? col.name }}
                      </span>
                      <span v-if="col.source_field" class="mapped-badge">
                        <i class="fa-solid fa-bolt"></i>
                        {{ $t("layouts.line_items_mapping_auto") }}
                      </span>

                      <select
                        class="editor__columns__item__select"
                        :value="col.source_field || ''"
                        @change="updateSourceField(index, $event.target.value)"
                        @click.stop
                      >
                        <option value="">
                          {{ $t("layouts.line_items_mapping_manual") }}
                        </option>
                        <option
                          v-for="sourceField in sourceFields"
                          :key="sourceField.name"
                          :value="sourceField.name"
                        >
                          {{ $t(sourceField.label) ?? sourceField.name }}
                        </option>
                      </select>

                      <button
                        class="editor__columns__item__remove"
                        type="button"
                        :title="$t('layouts.remove_column')"
                        @click="removeColumn(index)"
                      >
                        <i class="fa-solid fa-times"></i>
                      </button>
                    </div>

                    <div
                      class="editor__columns__drop-zone"
                      :class="{
                        'editor__columns__drop-zone--active': isDropZoneActive(
                          'columns',
                          index + 1,
                        ),
                      }"
                      @dragover="setDragOver('columns', index + 1, $event)"
                      @drop="onDropOnColumns(index + 1, $event)"
                    />
                  </div>
                </template>

                <div
                  v-if="internalColumns.length === 0"
                  class="editor__sections__item__content__empty"
                >
                  <p>{{ $t("layouts.drop_fields_here") }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="dragging"
      class="editor__ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
        width: ghostWidth || 'auto',
        height: ghostHeight || 'auto',
      }"
    >
      <span class="editor__ghost__handle">
        <i class="fa-solid fa-grip-vertical"></i>
      </span>
      <span class="editor__ghost__label">
        {{ ghostLabel }}
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>

.mapped-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  white-space: nowrap;
  color: var(--module-color, #3498db);
  background: color-mix(in srgb, var(--module-color, #3498db) 12%, var(--color-bg-surface));

  i {
    font-size: 0.65rem;
  }
}

.editor__columns__item__select {
  flex: 0 0 auto;
  width: 200px;
  padding: 6px 28px 6px 10px;
  border: 1px solid var(--color-border);
  border-radius: 6px;

  background: var(--color-bg-muted)
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%239ca3af'/%3E%3C/svg%3E")
    no-repeat right 10px center;
  appearance: none;
  font-size: 0.82rem;
  color: var(--color-text-strong);
  cursor: pointer;
  transition: all 0.15s ease;

  &:hover {
    border-color: var(--module-color, #3498db);
  }

  &:focus {
    outline: none;
    border-color: var(--module-color, #3498db);
    background-color: var(--color-bg-surface);
  }
}
</style>
