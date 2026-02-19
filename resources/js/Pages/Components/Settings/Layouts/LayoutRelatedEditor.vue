<script setup>
import { ref, watch, computed, getCurrentInstance, onBeforeUnmount } from "vue";
import LayoutRelatedFields from "./LayoutRelatedFields.vue";
const props = defineProps({
  panels: {
    type: Array,
    default: () => [],
  },
  availableRelationships: {
    type: Array,
    default: () => [],
  },
  relByKey: {
    type: Object,
    default: () => ({}),
  },
  emptyPanels: {
    type: Set,
    default: [],
  },
});
const emit = defineEmits(["update:panels"]);

const internalpanels = ref([...props.panels]);
const internalAvailable = ref([...props.availableRelationships]);
const confirmSectionIndex = ref(null);
const showSubpanels = ref([]);

const isConfirm = (index) => confirmSectionIndex.value === index;
watch(
  () => props.panels,
  (val) => {
    internalpanels.value = [...val];
  },
  { deep: true },
);
watch(
  () => props.availableRelationships,
  (val) => {
    internalAvailable.value = [...val];
  },
  { deep: true },
);

const hasEmptyPanelError = (index) => {
  const hasNoItems = props.panels[index].layout?.length === 0;
  return props.emptyPanels.has(index) && hasNoItems;
};
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
  internalpanels.value.forEach((section) => {
    (section.layout || []).forEach((col) => {
      if (col?.key) used.add(col.key);
    });
  });
  return used;
});

const filteredAvailableRelationships = computed(() => {
  return internalAvailable.value.filter(
    (field) => !usedFieldKeys.value.has(field.key),
  );
});
const ghostLabel = computed(() => {
  if (!dragging.value) return "";
  const { source, sectionIndex, columnIndex, isField } = dragging.value;

  if (source === "available") {
    const item = filteredAvailableRelationships.value[columnIndex];
    return item ? (t(item.label) ?? item.key) : "";
  } else if (source === "section") {
    const section = internalpanels.value[sectionIndex];
    const item = section?.layout?.[columnIndex];
    return item ? (t(item.label) ?? item.key) : "";
  }
  return "";
});

const addNewSection = () => {
  internalpanels.value.push({
    layout: [],
  });
  emitUpdatedpanels();
};

const removeSection = (sectionIndex) => {
  const hasLayout = internalpanels.value[sectionIndex]?.layout?.length > 0;
  if (confirmSectionIndex.value !== sectionIndex && hasLayout) {
    confirmSectionIndex.value = sectionIndex;
    return;
  }

  if (internalpanels.value.length > 1) {
    internalpanels.value.splice(sectionIndex, 1);
    emitUpdatedpanels();
  }
  confirmSectionIndex.value = null;
};

// Drag and drop functions
const startDrag = (source, sectionIndex, columnIndex, column, event) => {
  if (source === "section" && showSubpanels.value.includes(`${column.name}`)) {
    event.preventDefault();
    return;
  }
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

  const el = event.currentTarget;

  if (el) {
    ghostWidth.value = el.offsetWidth + "px";
    ghostHeight.value = el.offsetHeight + "px";

    const rect = el.getBoundingClientRect();

    originOffset.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };

    ghostRenderPos.value = {
      x: event.clientX,
      y: event.clientY,
    };
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
  const field = filteredAvailableRelationships.value[fieldIndex];
  if (!field) return;

  const panels = [...internalpanels.value];
  const targetSection = panels[targetSectionIndex];

  if (!targetSection.layout) targetSection.layout = [];
  const newColumn = {
    name: field.name,
    label: field.label,
    type: field.relationship_type,
    readonly: field.readonly,
    required: field.required,
    relationship: props.relByKey[field.name],
  };

  targetSection.layout.splice(targetColumnIndex, 0, newColumn);
  internalpanels.value = panels;
  emitUpdatedpanels();
};

const moveColumnWithinSection = (sectionIndex, fromIndex, toIndex) => {
  if (fromIndex === toIndex) return;
  const panels = [...internalpanels.value];
  const section = panels[sectionIndex];
  if (!section?.layout) return;

  const [item] = section.layout.splice(fromIndex, 1);
  section.layout.splice(toIndex, 0, item);
  internalpanels.value = panels;
  emitUpdatedpanels();
};

const moveColumnBetweenpanels = (
  fromSectionIndex,
  fromColumnIndex,
  toSectionIndex,
  toColumnIndex,
) => {
  const panels = [...internalpanels.value];
  const fromSection = panels[fromSectionIndex];
  const toSection = panels[toSectionIndex];

  if (!fromSection?.layout || !toSection?.layout) return;

  const [item] = fromSection.layout.splice(fromColumnIndex, 1);
  toSection.layout.splice(toColumnIndex, 0, item);

  internalpanels.value = panels;
  emitUpdatedpanels();
};

const removeColumnFromSection = (sectionIndex, columnIndex) => {
  const panels = [...internalpanels.value];
  const section = panels[sectionIndex];

  if (section?.layout) {
    section.layout.splice(columnIndex, 1);
    internalpanels.value = panels;
    emitUpdatedpanels();
  }
};

const toggleSubpanel = (column) => {
  const key = `${column.name}`;
  const index = showSubpanels.value.indexOf(key);

  if (index === -1) {
    showSubpanels.value.push(key);
  } else {
    showSubpanels.value.splice(index, 1);
  }
};
const isSubpanelOpen = (column) => {
  return showSubpanels.value.includes(`${column.name}`);
};

const updateColumnPanelHeader = (sectionIndex, columnIndex, value) => {
  const panels = [...internalpanels.value];

  panels[sectionIndex].layout[columnIndex].panelHeader = value;

  internalpanels.value = panels;
  emitUpdatedpanels();
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
      moveColumnBetweenpanels(
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
    const panels = [...internalpanels.value];
    const targetSection = panels[sectionIndex];
    const sourceSection = panels[dragSectionIndex];

    if (!targetSection.layout) targetSection.layout = [];

    const [item] = sourceSection.layout.splice(dragColumnIndex, 1);
    targetSection.layout.push(item);

    internalpanels.value = panels;
    emitUpdatedpanels();
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

const emitUpdatedpanels = () => {
  emit("update:panels", internalpanels.value);
};

const updateSectionName = (sectionIndex, name) => {
  internalpanels.value[sectionIndex].name = name;
  emitUpdatedpanels();
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
            $t("layouts.available_relationships")
          }}</span>
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
            v-for="(field, index) in filteredAvailableRelationships"
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
            @dragstart="startDrag('available', 0, index, null, $event)"
            @dragend="endDrag"
          >
            <span class="editor__available-fields__item__handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </span>
            <span class="editor__available-fields__item__label">
              {{ $t(field.label) ?? field.key }}
            </span>
            <span class="editor__available-fields__item__type">
              {{ $t("relationships.types." + field.relationship_type) }}
            </span>
          </div>

          <div
            v-if="filteredAvailableRelationships.length === 0"
            class="editor__available-fields__no-fields"
          >
            {{ $t("layouts.all_relationships_used") }}
          </div>
        </div>
      </div>

      <div class="editor__container__main">
        <div class="editor__container__main__header">
          <div class="editor__container__main__header__title">
            {{ $t("layouts.related") }}
          </div>
          <button
            @click="addNewSection"
            class="editor__container__main__header__btn btn"
            type="button"
            :disabled="internalpanels.length > 2"
          >
            <i class="fa-solid fa-plus"></i> {{ $t("layouts.add_column") }}
          </button>
        </div>
        <div class="editor__related">
          <div
            v-for="(section, sectionIndex) in internalpanels"
            :key="sectionIndex"
            :class="[
              'editor__related__column',
              { 'is-empty': hasEmptyPanelError(sectionIndex) },
            ]"
          >
            <div
              class="editor__related__column__error"
              v-if="hasEmptyPanelError(sectionIndex)"
            >
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div class="editor__related__column__actions">
              <button
                v-if="internalpanels.length > 1"
                @click="removeSection(sectionIndex)"
                :class="[
                  'remove-section',
                  isConfirm(sectionIndex) ? 'confirm-remove' : 'show-remove',
                ]"
                type="button"
              >
                <i
                  :class="[
                    'fa-solid',
                    isConfirm(sectionIndex) ? 'fa-check' : 'fa-trash',
                  ]"
                />
              </button>
            </div>
            <div class="editor__related__column__content">
              <div
                v-if="!section.layout || section.layout.length === 0"
                class="editor__related__column__content__empty"
                :class="[
                  {
                    'editor__related__column__content__empty--active':
                      isDropZoneActive('section-empty', sectionIndex, 0),
                  },
                  {
                    'editor__related__column__content__empty--has-empty-error':
                      hasEmptyPanelError(sectionIndex),
                  },
                ]"
                @dragover="
                  setDragOver('section-empty', sectionIndex, 0, $event)
                "
                @drop="onDropOnSectionEmpty(sectionIndex, $event)"
              >
                <p>{{ $t("layouts.drop_boxes_here") }}</p>
              </div>

              <div v-else class="editor__related__list">
                <div
                  class="editor__related__list__drop-zone editor__related__drop-zone--horizontal"
                  :class="{
                    'editor__related__list__drop-zone--active':
                      isDropZoneActive('section-column', sectionIndex, 0),
                  }"
                  @dragover="
                    setDragOver('section-column', sectionIndex, 0, $event)
                  "
                  @drop="onDropOnSectionColumn(sectionIndex, 0, $event)"
                />

                <div
                  v-for="(column, columnIndex) in section.layout"
                  :key="columnIndex"
                  class="editor__related__list__row"
                  :class="{
                    'editor__related__list__row--dragging': isItemDragging(
                      'section',
                      sectionIndex,
                      columnIndex,
                    ),
                    'editor__related__list__row--locked':
                      isSubpanelOpen(column),
                  }"
                >
                  <div class="editor__related__list__row__wrapper">
                    <div
                      class="editor__related__list__row__content"
                      draggable="true"
                      @dragstart="
                        startDrag(
                          'section',
                          sectionIndex,
                          columnIndex,
                          column,
                          $event,
                        )
                      "
                      @dragend="endDrag"
                    >
                      <span class="editor__related__list__row__handle">
                        <i class="fa-solid fa-grip-vertical"></i>
                      </span>
                      <span class="editor__related__list__row__label">
                        <span>{{ $t(column.label) ?? column.key }}</span>

                        <span
                          v-if="isSubpanelOpen(column)"
                          class="editor__related__column__label__flag"
                          ><i class="fa-solid fa-lock"></i
                        ></span>
                      </span>
                      <button
                        @click="toggleSubpanel(column)"
                        class="editor__related__list__row__edit"
                        type="button"
                        :title="
                          isSubpanelOpen(column)
                            ? $t('layouts.save_column')
                            : $t('layouts.edit_column')
                        "
                      >
                        <i
                          :class="[
                            'fa-solid',
                            isSubpanelOpen(column)
                              ? 'fa-floppy-disk'
                              : 'fa-pen-to-square',
                          ]"
                        ></i>
                      </button>
                      <button
                        @click="
                          removeColumnFromSection(sectionIndex, columnIndex)
                        "
                        class="editor__related__list__row__remove"
                        type="button"
                        :title="$t('layouts.remove_column')"
                      >
                        <i class="fa-solid fa-times"></i>
                      </button>
                    </div>

                    <div class="related-fields">
                      <LayoutRelatedFields
                        :fields="relByKey[column.name]"
                        :showFields="isSubpanelOpen(column)"
                        :selectedFields="column.panelHeader || []"
                        @update:selectedFields="
                          (val) =>
                            updateColumnPanelHeader(
                              sectionIndex,
                              columnIndex,
                              val,
                            )
                        "
                      ></LayoutRelatedFields>
                    </div>
                  </div>

                  <div
                    class="editor__related__list__drop-zone editor__related__list__drop-zone--horizontal"
                    :class="{
                      'editor__related__list__drop-zone--active':
                        isDropZoneActive(
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
        <span class="related-fields__hint">
          <i class="fa-solid fa-asterisk"></i>
          {{ $t("layouts.fields_header_hint") }}
        </span>
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
