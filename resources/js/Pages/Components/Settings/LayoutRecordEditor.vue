<script setup>
import { ref, watch, computed, getCurrentInstance, onBeforeUnmount } from "vue";

const props = defineProps({
  sections: {
    type: Array,
    default: () => [],
  },
  availableFields: {
    type: Array,
    default: () => [],
  },
  fieldByKey: {
    type: Object,
    default: () => ({}),
  },
});
console.log(props.sections);

const emit = defineEmits(["update:sections"]);

const internalSections = ref([...props.sections]);
const internalAvailable = ref([...props.availableFields]);
watch(
  () => props.sections,
  (val) => {
    internalSections.value = [...val];
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

const usedFieldKeys = computed(() => {
  const used = new Set();
  internalSections.value.forEach((section) => {
    (section.layout || []).forEach((col) => {
      if (col?.key) used.add(col.key);
    });
  });
  return used;
});

const filteredAvailableFields = computed(() => {
  return internalAvailable.value.filter(
    (field) => !usedFieldKeys.value.has(field.key),
  );
});

const ghostLabel = computed(() => {
  if (!dragging.value) return "";
  const { source, sectionIndex, columnIndex, isField } = dragging.value;

  if (source === "available") {
    const item = filteredAvailableFields.value[columnIndex];
    return item ? (t(item.label) ?? item.key) : "";
  } else if (source === "section") {
    const section = internalSections.value[sectionIndex];
    const item = section?.layout?.[columnIndex];
    return item ? (t(item.label) ?? item.key) : "";
  }
  return "";
});

const addNewSection = () => {
  internalSections.value.push({
    name: `Section ${internalSections.value.length + 1}`,
    layout: [],
  });
  emitUpdatedSections();
};

const removeSection = (sectionIndex) => {
  if (internalSections.value.length > 1) {
    internalSections.value.splice(sectionIndex, 1);
    emitUpdatedSections();
  }
};

// Drag and drop functions
const startDrag = (source, sectionIndex, columnIndex, event) => {
  const isField = source === "available";
  dragging.value = {
    source,
    sectionIndex,
    columnIndex,
    isField,
  };

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = "move";
    event.dataTransfer.setData(
      "text/plain",
      `${source}:${sectionIndex}:${columnIndex}`,
    );
    try {
      event.dataTransfer.setDragImage(dragImage, 0, 0);
    } catch (e) {}
  }

  const el = event.target.closest(".rle-item, .rle-field-item");
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
  stopGhostAnimation();
};

const setDragOver = (target, sectionIndex, columnIndex, event) => {
  event.preventDefault();
  dragOver.value = { target, sectionIndex, columnIndex };
};

const isItemDragging = (source, sectionIndex, columnIndex) => {
  if (!dragging.value) return false;
  return (
    dragging.value.source === source &&
    dragging.value.sectionIndex === sectionIndex &&
    dragging.value.columnIndex === columnIndex
  );
};

const isDropZoneActive = (target, sectionIndex, columnIndex) => {
  if (!dragOver.value) return false;
  return (
    dragOver.value.target === target &&
    dragOver.value.sectionIndex === sectionIndex &&
    dragOver.value.columnIndex === columnIndex
  );
};

// Move operations
const moveFieldToSection = (
  fieldIndex,
  targetSectionIndex,
  targetColumnIndex,
) => {
  const field = filteredAvailableFields.value[fieldIndex];
  if (!field) return;

  const sections = [...internalSections.value];
  const targetSection = sections[targetSectionIndex];

  if (!targetSection.layout) targetSection.layout = [];

  const newColumn = {
    name: field.name,
    label: field.label,
    type: field.type,
    readonly: field.readonly,
    required: field.required,
    field: props.fieldByKey[field.name],
  };

  targetSection.layout.splice(targetColumnIndex, 0, newColumn);
  internalSections.value = sections;
  emitUpdatedSections();
};

const moveColumnWithinSection = (sectionIndex, fromIndex, toIndex) => {
  if (fromIndex === toIndex) return;
  const sections = [...internalSections.value];
  const section = sections[sectionIndex];
  if (!section?.layout) return;

  const [item] = section.layout.splice(fromIndex, 1);
  section.layout.splice(toIndex, 0, item);
  internalSections.value = sections;
  emitUpdatedSections();
};

const moveColumnBetweenSections = (
  fromSectionIndex,
  fromColumnIndex,
  toSectionIndex,
  toColumnIndex,
) => {
  const sections = [...internalSections.value];
  const fromSection = sections[fromSectionIndex];
  const toSection = sections[toSectionIndex];

  if (!fromSection?.layout || !toSection?.layout) return;

  const [item] = fromSection.layout.splice(fromColumnIndex, 1);
  toSection.layout.splice(toColumnIndex, 0, item);

  internalSections.value = sections;
  emitUpdatedSections();
};

const removeColumnFromSection = (sectionIndex, columnIndex) => {
  const sections = [...internalSections.value];
  const section = sections[sectionIndex];

  if (section?.layout) {
    section.layout.splice(columnIndex, 1);
    internalSections.value = sections;
    emitUpdatedSections();
  }
};

// Drop handlers
const onDropOnAvailable = (targetIndex, event) => {
  event.preventDefault();
  if (!dragging.value || dragging.value.source !== "section") return;

  const { sectionIndex, columnIndex } = dragging.value;
  removeColumnFromSection(sectionIndex, columnIndex);
  endDrag();
};

const onDropOnSectionColumn = (sectionIndex, columnIndex, event) => {
  event.preventDefault();
  if (!dragging.value) return;

  const {
    source,
    sectionIndex: dragSectionIndex,
    columnIndex: dragColumnIndex,
  } = dragging.value;

  if (source === "available") {
    moveFieldToSection(dragColumnIndex, sectionIndex, columnIndex);
  } else if (source === "section") {
    if (dragSectionIndex === sectionIndex) {
      moveColumnWithinSection(sectionIndex, dragColumnIndex, columnIndex);
    } else {
      moveColumnBetweenSections(
        dragSectionIndex,
        dragColumnIndex,
        sectionIndex,
        columnIndex,
      );
    }
  }

  endDrag();
};

const onDropOnSectionEmpty = (sectionIndex, event) => {
  event.preventDefault();
  if (!dragging.value) return;

  const {
    source,
    sectionIndex: dragSectionIndex,
    columnIndex: dragColumnIndex,
  } = dragging.value;

  if (source === "available") {
    moveFieldToSection(dragColumnIndex, sectionIndex, 0);
  } else if (source === "section") {
    const sections = [...internalSections.value];
    const targetSection = sections[sectionIndex];
    const sourceSection = sections[dragSectionIndex];

    if (!targetSection.layout) targetSection.layout = [];

    const [item] = sourceSection.layout.splice(dragColumnIndex, 1);
    targetSection.layout.push(item);

    internalSections.value = sections;
    emitUpdatedSections();
  }

  endDrag();
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

const emitUpdatedSections = () => {
  emit("update:sections", internalSections.value);
};

const updateSectionName = (sectionIndex, name) => {
  internalSections.value[sectionIndex].name = name;
  emitUpdatedSections();
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
          <h5>{{ $t("layouts.available_fields") }}</h5>
          <small>{{ $t("layouts.drag_to_sections") }}</small>
        </div>

        <div class="editor__available-fields">
          <div
            class="editor__empty-drop-zone"
            :class="{
              'editor__empty-drop-zone--active': isDropZoneActive(
                'available',
                0,
                0,
              ),
            }"
            @dragover="setDragOver('available', 0, 0, $event)"
            @drop="onDropOnAvailable(0, $event)"
          >
            {{ $t("layouts.drop_here_to_remove") }}
          </div>

          <div
            v-for="(field, index) in filteredAvailableFields"
            :key="field.key"
            class="editor__available-fields__item"
            :class="{
              'editor__available-fields__item--dragging': isItemDragging(
                'available',
                0,
                index,
              ),
            }"
            draggable="true"
            @dragstart="startDrag('available', 0, index, $event)"
            @dragend="endDrag"
          >
            <span class="editor__available-fields__item__handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </span>
            <span class="editor__available-fields__item__label">
              {{ $t(field.label) ?? field.key }}
            </span>
          </div>

          <div
            v-if="filteredAvailableFields.length === 0"
            class="editor__available-fields__no-fields"
          >
            {{ $t("layouts.all_fields_used") }}
          </div>
        </div>
      </div>

      <div class="editor__container__main">
        <div class="editor__container__main__header">
          <h3>{{ $t("layouts.record_sections") }}</h3>
          <button
            @click="addNewSection"
            class="editor__container__main__header__btn btn"
            type="button"
          >
            <i class="fa-solid fa-plus"></i> {{ $t("layouts.add_section") }}
          </button>
        </div>

        <div class="editor__sections">
          <div
            v-for="(section, sectionIndex) in internalSections"
            :key="sectionIndex"
            class="editor__sections__item"
          >
            <div class="editor__sections__item__header">
              <div class="editor__sections__item__header__title">
                <input
                  :value="section.name"
                  @input="updateSectionName(sectionIndex, $event.target.value)"
                  type="text"
                  :placeholder="$t('layouts.section_name_placeholder')"
                />
              </div>
              <div class="editor__sections__item__header__actions">
                <button
                  v-if="internalSections.length > 1"
                  @click="removeSection(sectionIndex)"
                  class="btn"
                  type="button"
                  :title="$t('layouts.remove_section')"
                >
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>

            <div class="editor__sections__item__content">
              <div
                v-if="!section.layout || section.layout.length === 0"
                class="editor__sections__item__content__empty"
                :class="{
                  'editor__sections__item__content__empty--active':
                    isDropZoneActive('section-empty', sectionIndex, 0),
                }"
                @dragover="
                  setDragOver('section-empty', sectionIndex, 0, $event)
                "
                @drop="onDropOnSectionEmpty(sectionIndex, $event)"
              >
                <p>{{ $t("layouts.drop_fields_here") }}</p>
              </div>

              <div v-else class="editor__columns">
                <div
                  class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
                  :class="{
                    'editor__columns__drop-zone--active': isDropZoneActive(
                      'section-column',
                      sectionIndex,
                      0,
                    ),
                  }"
                  @dragover="
                    setDragOver('section-column', sectionIndex, 0, $event)
                  "
                  @drop="onDropOnSectionColumn(sectionIndex, 0, $event)"
                />

                <div
                  v-for="(column, columnIndex) in section.layout"
                  :key="columnIndex"
                  class="editor__columns__item"
                  :class="{
                    'editor__columns__item--dragging': isItemDragging(
                      'section',
                      sectionIndex,
                      columnIndex,
                    ),
                  }"
                >
                  <div
                    class="editor__columns__item__content"
                    draggable="true"
                    @dragstart="
                      startDrag('section', sectionIndex, columnIndex, $event)
                    "
                    @dragend="endDrag"
                  >
                    <span class="editor__columns__item__handle">
                      <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                    <span class="editor__columns__item__label">
                      {{ $t(column.label) ?? column.key }}
                    </span>
                    <button
                      @click="
                        removeColumnFromSection(sectionIndex, columnIndex)
                      "
                      class="editor__columns__item__remove"
                      type="button"
                      :title="$t('layouts.remove_column')"
                    >
                      <i class="fa-solid fa-times"></i>
                    </button>
                  </div>

                  <!-- Drop zone after column -->
                  <div
                    class="editor__columns__drop-zone editor__columns__drop-zone--horizontal"
                    :class="{
                      'editor__columns__drop-zone--active': isDropZoneActive(
                        'section-column',
                        sectionIndex,
                        columnIndex + 1,
                      ),
                    }"
                    @dragover="
                      setDragOver(
                        'section-column',
                        sectionIndex,
                        columnIndex + 1,
                        $event,
                      )
                    "
                    @drop="
                      onDropOnSectionColumn(
                        sectionIndex,
                        columnIndex + 1,
                        $event,
                      )
                    "
                  />
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
