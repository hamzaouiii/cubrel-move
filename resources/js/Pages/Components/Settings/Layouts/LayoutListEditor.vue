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
const dragTrails = ref([]);
const ghostRenderPos = ref({ x: 0, y: 0 });

let ghostAnimationFrame = null;

const transparentPixel = "data:image/gif;base64,R0lGODlhAQABAAAAACw=";
const dragImage = new Image();
dragImage.src = transparentPixel;

const ghostLabel = computed(() => {
  if (!dragging.value) return "";
  const { list, index } = dragging.value;
  if (list === "available") {
    const item = internalAvailable.value[index];
    return item ? (t(item.label) ?? item.key) : "";
  } else {
    const item = internalColumns.value[index];
    return item ? (t(item.label) ?? item.key) : "";
  }
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
  const el = event.target.closest(".lle-item");
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
  dragTrails.value = [];
};

const setDragOver = (listName, index, event) => {
  event.preventDefault();
  dragOver.value = { list: listName, index };
};

const isItemDragging = (listName, index) => {
  return (
    dragging.value &&
    dragging.value.list === listName &&
    dragging.value.index === index
  );
};

const isDropZoneActive = (listName, index) => {
  return (
    dragOver.value &&
    dragOver.value.list === listName &&
    dragOver.value.index === index
  );
};

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
  to.splice(toIndex, 0, item);
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
const removeColumnFromList = (columnIndex) => {
  const columns = [...internalColumns.value];
  const column = columns[columnIndex];
  if (column) {
    columns.splice(columnIndex, 1);
    internalColumns.value = columns;
    emitUpdatedColumns();
  }
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

const allowDrop = (event) => {
  event.preventDefault();
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
</script>

<template>
  <div class="editor" @dragover="onGlobalDragOver">
    <div class="editor__container">
      <div class="editor__container__sidebar">
        <div class="editor__container__sidebar__header">
          <span class="editor__container__sidebar__header__title">{{
            $t("layouts.available_fields")
          }}</span>
          <small>{{ $t("layouts.available_fields_hint") }}</small>
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
            :key="field.key"
          >
            <li
              class="editor__available-fields__item"
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
                {{ $t(field.label) ?? field.key }}
              </span>
              <span class="editor__available-fields__item__type">
                {{ $t("fields.types." + field.type) }}
              </span>
            </li>

            <!-- Drop zone *after* this item -->
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
              {{ $t("layouts.list_columns") }}
            </span>
            <small>{{ $t("layouts.list_columns_hint") }}</small>
          </div>
          <div class="editor__sections__item">
            <div class="editor__sections__item__content">
              <div class="editor__columns">
                <div
                  class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
                  :class="{
                    'editor__columns__drop-zone--active': isDropZoneActive(
                      'columns',
                      0,
                    ),
                  }"
                  @dragover="setDragOver('columns', 0, $event)"
                  @drop="onDropOnColumns(0, $event)"
                />

                <!-- Column items -->
                <template
                  v-for="(col, index) in internalColumns"
                  :key="col.key"
                >
                  <div
                    class="editor__columns__item"
                    :class="{
                      'editor__columns__item--dragging': isItemDragging(
                        'columns',
                        index,
                      ),
                    }"
                  >
                    <!-- Column content -->
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
                        {{ $t(col.label) ?? col.key }}
                      </span>

                      <button
                        class="editor__columns__item__remove"
                        type="button"
                        :title="$t('layouts.remove_column')"
                        @click="removeColumnFromList(index)"
                      >
                        <i class="fa-solid fa-times"></i>
                      </button>
                    </div>

                    <!-- Drop zone after column -->
                    <div
                      class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
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
