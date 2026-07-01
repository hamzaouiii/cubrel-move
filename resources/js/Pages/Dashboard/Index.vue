<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import axios from "axios";
import { useLayoutDragDrop } from "@/Composables/useLayoutDragDrop.js";

import {
  WIDGET_REGISTRY,
  WIDGET_TYPES,
} from "@/Pages/Components/Dashbaord/WidgetRegistry.js";
import AddWidgetPanel from "@/Pages/Components/Dashbaord/AddWidgetPanel.vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
  ownedRecords: { type: Array, default: () => [] },
  dashboardLayout: { type: Array, default: () => [] },
  dashboardModules: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props?.auth?.user || {});
const layout = ref([...props.dashboardLayout]);
const showPanel = ref(false);
const editMode = ref(false);
const savedLayout = ref([]);
const editingInstance = ref(null);

// ── Ghost animation (from composable) ────────────────────────────────────────
const {
  originOffset,
  ghostWidth,
  ghostRenderPos,
  beginDrag,
  endDrag,
  onGlobalDragOver,
} = useLayoutDragDrop();

// ── Drag state ────────────────────────────────────────────────────────────────
const draggingId = ref(null); // id of the widget being dragged
const dropBeforeId = ref(null); // id of the widget the placeholder appears before (null = append)

// ── Widgets ───────────────────────────────────────────────────────────────────

const activeWidgets = computed(() =>
  layout.value
    .map((item) => {
      if (typeof item === "string") {
        const def = WIDGET_REGISTRY[item];
        if (!def) return null;
        return {
          id: item,
          cols: def.cols,
          component: def.component,
          resolvedProps: def.getProps(props),
          label: def.label || item,
          isConfigurable: false,
        };
      } else {
        const def = WIDGET_TYPES[item.type];
        if (!def) return null;
        return {
          id: item.instanceId,
          cols: item.cols,
          component: def.component,
          resolvedProps: { instance: item },
          label: item.config?.label || item.config?.module || item.type,
          isConfigurable: true,
        };
      }
    })
    .filter(Boolean),
);

// During drag: remove the dragged widget from its slot and re-insert it as a
// placeholder before `dropBeforeId` so the other widgets visually shift.
const previewWidgets = computed(() => {
  if (!draggingId.value) return activeWidgets.value;

  const dragged = activeWidgets.value.find((w) => w.id === draggingId.value);
  if (!dragged) return activeWidgets.value;

  const rest = activeWidgets.value.filter((w) => w.id !== draggingId.value);

  const placeholder = { ...dragged, isPlaceholder: true };

  if (!dropBeforeId.value) return [...rest, placeholder];

  const insertAt = rest.findIndex((w) => w.id === dropBeforeId.value);
  if (insertAt === -1) return [...rest, placeholder];

  return [...rest.slice(0, insertAt), placeholder, ...rest.slice(insertAt)];
});

const ghostLabel = computed(() => {
  if (!draggingId.value) return "";
  return (
    activeWidgets.value.find((w) => w.id === draggingId.value)?.label || ""
  );
});

// ── Widget refresh ────────────────────────────────────────────────────────────

const widgetRefs = {}

function setWidgetRef(id, el) {
  if (el) widgetRefs[id] = el;
  else delete widgetRefs[id];
}

function refreshAll() {
  Object.values(widgetRefs).forEach((w) => w?.load?.());
}

// ── Layout persistence ────────────────────────────────────────────────────────

function enterEdit() {
  savedLayout.value = [...layout.value];
  editMode.value = true;
  showPanel.value = false;
}

function saveLayout() {
  persistLayout();
  editMode.value = false;
}

function cancelEdit() {
  layout.value = [...savedLayout.value];
  editMode.value = false;
}

function removeWidget(id) {
  layout.value = layout.value.filter((w) =>
    typeof w === "string" ? w !== id : w.instanceId !== id,
  );
  if (!editMode.value) persistLayout();
}

function addWidget(id) {
  if (!layout.value.some((w) => w === id)) {
    layout.value = [...layout.value, id];
    if (!editMode.value) persistLayout();
  }
}

function addInstance(instance) {
  layout.value = [...layout.value, instance];
  if (!editMode.value) persistLayout();
}

function openEdit(widgetId) {
  const item = layout.value.find(
    (l) => typeof l !== "string" && l.instanceId === widgetId,
  );
  if (!item) return;
  editingInstance.value = item;
  showPanel.value = true;
}

function updateInstance(updated) {
  layout.value = layout.value.map((l) =>
    typeof l !== "string" && l.instanceId === updated.instanceId ? updated : l,
  );
  editingInstance.value = null;
  showPanel.value = false;
}

async function persistLayout() {
  try {
    await axios.post("/dashboard/layout", { layout: layout.value });
  } catch {
    // layout stays updated locally even if save fails
  }
}

// ── Drag handlers ─────────────────────────────────────────────────────────────

let hoveredDropWidget = null; // track which widget the cursor is currently over

function startDrag(widgetId, event) {
  // Start placeholder at the natural position (before the next sibling)
  const idx = activeWidgets.value.findIndex((w) => w.id === widgetId);
  const nextSibling = activeWidgets.value[idx + 1];

  hoveredDropWidget = null;
  draggingId.value = widgetId;
  dropBeforeId.value = nextSibling?.id ?? null;

  beginDrag(true, event, ".w-cell");
}

function setDropTarget(widgetId, event) {
  event.preventDefault(); // always needed so the browser allows dropping
  if (widgetId === draggingId.value) return;
  if (widgetId === hoveredDropWidget) return; // same widget — skip to avoid feedback loop

  hoveredDropWidget = widgetId;

  // Split the cell horizontally: left half → insert before, right half → insert after
  const rect = event.currentTarget.getBoundingClientRect();
  const isRightHalf = event.clientX - rect.left > rect.width / 2;

  if (isRightHalf) {
    const idx = activeWidgets.value.findIndex((w) => w.id === widgetId);
    const next = activeWidgets.value[idx + 1];
    dropBeforeId.value = next?.id ?? null;
  } else {
    dropBeforeId.value = widgetId;
  }
}

function handleDrop(event) {
  event.preventDefault();
  if (!draggingId.value) return;

  // Derive new layout order from the current preview
  layout.value = previewWidgets.value
    .map((w) =>
      layout.value.find((l) =>
        typeof l === "string" ? l === w.id : l.instanceId === w.id,
      ),
    )
    .filter(Boolean);

  resetDrag();
}

function resetDrag() {
  hoveredDropWidget = null;
  draggingId.value = null;
  dropBeforeId.value = null;
  endDrag();
  nextTick(() => { applyMasonrySpans(); observeCells(); });
}

// ── Masonry row spans ─────────────────────────────────────────────────────────

const ROW_UNIT = 8;
let resizeObs = null;

function applyMasonrySpans() {
  if (draggingId.value) return; // don't thrash during drag
  const grid = document.querySelector(".dashboard__grid");
  if (!grid) return;
  Array.from(grid.children).forEach((cell) => {
    if (!(cell instanceof HTMLElement)) return;
    cell.style.gridRowEnd = `span ${Math.ceil(cell.offsetHeight / ROW_UNIT)}`;
  });
}

function observeCells() {
  resizeObs?.disconnect();
  resizeObs = new ResizeObserver(applyMasonrySpans);
  const grid = document.querySelector(".dashboard__grid");
  if (!grid) return;
  Array.from(grid.children).forEach((cell) => resizeObs.observe(cell));
}

onMounted(() =>
  nextTick(() => {
    applyMasonrySpans();
    observeCells();
  }),
);
onUnmounted(() => resizeObs?.disconnect());
watch(previewWidgets, () =>
  nextTick(() => {
    applyMasonrySpans();
    observeCells();
  }),
);
</script>

<template>
  <Head>
    <title>Dashboard - Cubrel</title>
  </Head>

  <div
    class="dashboard"
    @dragover="onGlobalDragOver"
    @drop.prevent="handleDrop"
  >
    <!-- Header -->
    <div class="dashboard__header dashboard__card">
      <div class="dashboard__header-left">
        <h1 class="dashboard__title">
          {{ $t("globals.dashboard.hi") }} {{ user.first_name }}!
        </h1>
        <p class="dashboard__subtitle">
          {{ $t("globals.dashboard.subtitle") }}
        </p>
      </div>
      <div class="dashboard__actions">
        <template v-if="!editMode">
          <button :title="$t('globals.dashboard.refresh_all')" @click="refreshAll">
            <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
          </button>
          <button
            :title="$t('globals.dashboard.customize_title')"
            @click="showPanel = true"
          >
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
          </button>
          <button
            class="primary"
            :title="$t('globals.dashboard.edit_layout')"
            @click="enterEdit"
          >
            <i class="fa-solid fa-pen" aria-hidden="true"></i>
          </button>
        </template>
        <template v-else>
          <button @click="cancelEdit">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
          <button class="primary" @click="saveLayout">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
          </button>
        </template>
      </div>
    </div>

    <TransitionGroup name="w-move" tag="div" class="dashboard__grid">
      <div
        v-for="widget in previewWidgets"
        :key="widget.id"
        class="w-cell"
        :data-cols="widget.cols"
        :style="{ gridColumn: `span ${widget.cols}` }"
        :class="{ 'w-cell--placeholder': editMode && widget.isPlaceholder }"
        :draggable="editMode && !widget.isPlaceholder ? 'true' : 'false'"
        @dragstart="
          editMode && !widget.isPlaceholder && startDrag(widget.id, $event)
        "
        @dragend="editMode && resetDrag()"
        @dragover="editMode && setDropTarget(widget.id, $event)"
        @drop.prevent="editMode && handleDrop($event)"
      >
        <div class="w-cell__inner">
          <!-- Placeholder: visible drop zone, no content -->
          <div v-if="widget.isPlaceholder" class="w-cell__drop-zone">
            <i class="fa-solid fa-grip-vertical"></i>
            <span>{{ ghostLabel ? $t(ghostLabel) : "" }}</span>
          </div>

          <!-- Normal widget -->
          <template v-else>
            <component
              :is="widget.component"
              v-bind="widget.resolvedProps"
              :ref="(el) => setWidgetRef(widget.id, el)"
            />
            <button
              v-if="editMode && widget.isConfigurable"
              class="w-cell__edit"
              :title="$t('globals.dashboard.edit_widget')"
              @click="openEdit(widget.id)"
            >
              <i class="fa-solid fa-pen"></i>
            </button>
            <button
              v-if="editMode"
              class="w-cell__remove w-cell__remove--editing"
              :title="$t('globals.dashboard.remove_widget')"
              @click="removeWidget(widget.id)"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
            <div v-if="editMode" class="w-cell__handle">
              <i class="fa-solid fa-grip-vertical"></i>
            </div>
          </template>
        </div>
      </div>
    </TransitionGroup>

    <!-- Empty state -->
    <div v-if="!activeWidgets.length" class="dashboard__empty">
      <i class="fa-regular fa-grid-2" aria-hidden="true"></i>
      <p>{{ $t("globals.dashboard.empty_title") }}</p>
      <button class="btn btn--primary" @click="showPanel = true">
        <i class="fa-solid fa-plus"></i>
        {{ $t("globals.dashboard.add_widgets") }}
      </button>
    </div>

    <!-- Drag ghost: floating label strip that follows the cursor -->
    <div
      v-if="editMode && draggingId"
      class="dashboard__ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
        width: ghostWidth || 'auto',
      }"
    >
      <i class="fa-solid fa-grip-vertical dashboard__ghost__handle"></i>
      <span class="dashboard__ghost__label">{{
        ghostLabel ? $t(ghostLabel) : ""
      }}</span>
    </div>

    <!-- Customize modal -->
    <AddWidgetPanel
      v-if="showPanel"
      :active-layout="layout"
      :modules="dashboardModules"
      :editing-instance="editingInstance"
      @close="
        showPanel = false;
        editingInstance = null;
      "
      @add="addWidget"
      @remove="removeWidget"
      @add-instance="addInstance"
      @update-instance="updateInstance"
    />
  </div>
</template>
