<script setup>
import { ref, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useLayoutDragDrop } from "@/Composables/useLayoutDragDrop";

const props = defineProps({
  sections: { type: Array, default: () => [] },
  availableFields: { type: Array, default: () => [] },
  availableRelationships: { type: Array, default: () => [] },
  lineItemFields: { type: Array, default: () => [] },
  moduleLabel: { type: String, default: "" },
});

const emit = defineEmits(["update:sections"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const page = usePage();
const company = computed(() => page.props.appSettings || {});
const companyInitials = computed(() => {
  const name = company.value.company_name || "";
  return (
    name
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map((w) => w[0]?.toUpperCase() ?? "")
      .join("") || "CO"
  );
});

const internalSections = ref(cloneSections(props.sections));
const internalAvailable = ref([...props.availableFields]);
const internalAvailableRelationships = ref([...props.availableRelationships]);
const openPickerIndex = ref(null);
const openLiPickerIndex = ref(null);
console.log(props.availableRelationships);

watch(
  () => props.sections,
  (val) => {
    internalSections.value = cloneSections(val);
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
watch(
  () => props.availableRelationships,
  (val) => {
    internalAvailableRelationships.value = [...val];
  },
  { deep: true },
);

function cloneSections(sections) {
  return (sections || []).map((s) => {
    const cloned = { ...s };
    if (s.type === "fields" || s.type === "header")
      cloned.items = (s.items || []).map((i) => ({ ...i }));
    if (s.type === "relationship")
      cloned.columns = (s.columns || []).map((c) => ({ ...c }));
    if (s.type === "line_items")
      cloned.columns = (s.columns || []).map((c) => ({ ...c }));
    return cloned;
  });
}

const {
  dragging,
  dragOver,
  originOffset,
  ghostWidth,
  ghostHeight,
  ghostRenderPos,
  beginDrag,
  endDrag,
  setDragOver,
  onGlobalDragOver,
} = useLayoutDragDrop();

// ── Computed ───────────────────────────────────────────────────────────────

const usedFieldNames = computed(() => {
  const used = new Set();
  internalSections.value.forEach((section) => {
    if (section.type === "fields" || section.type === "header") {
      (section.items || []).forEach((item) => {
        if (item.kind === "field" && !item.relationship) used.add(item.name);
      });
    }
  });
  return used;
});

const filteredAvailableFields = computed(() =>
  internalAvailable.value.filter((f) => !usedFieldNames.value.has(f.name)),
);

// Track rel fields used as "relName:fieldName"
const usedRelFieldKeys = computed(() => {
  const used = new Set();
  internalSections.value.forEach((section) => {
    if (section.type === "fields" || section.type === "header") {
      (section.items || []).forEach((item) => {
        if (item.kind === "field" && item.relationship)
          used.add(`${item.relationship}:${item.name}`);
      });
    }
  });
  return used;
});

// util

const getRelatedModuleLable = (rel) => {
  const other_side = rel.other_side;
};

// ── Relationship field picker (sidebar) ────────────────────────────────────
const expandedRelationships = ref({});
const relSearchQueries = ref({});

const toggleRelExpanded = (relName) => {
  expandedRelationships.value = {
    ...expandedRelationships.value,
    [relName]: !expandedRelationships.value[relName],
  };
};

const getAvailableRelFields = (rel) => {
  const query = (relSearchQueries.value[rel.name] || "").toLowerCase().trim();
  return (rel.related_fields || []).filter((f) => {
    if (usedRelFieldKeys.value.has(`${rel.name}:${f.name}`)) return false;
    if (!query) return true;
    const lbl = (typeof f.label === "string" ? f.label : f.name).toLowerCase();
    return lbl.includes(query) || f.name.toLowerCase().includes(query);
  });
};

const isRelFieldDragging = (relName, fieldName) =>
  dragging.value?.source === "available-rel-field" &&
  dragging.value.relName === relName &&
  dragging.value.fieldName === fieldName;

const onRelFieldDragStart = (relName, fieldName, event) => {
  beginDrag(
    { source: "available-rel-field", relName, fieldName },
    event,
    ".ple-available-item",
  );
};

const ghostLabel = computed(() => {
  if (!dragging.value) return "";
  const d = dragging.value;
  if (d.source === "available") {
    const f = filteredAvailableFields.value[d.fieldIndex];
    return f ? (t(f.label) ?? f.name) : "";
  }
  if (d.source === "available-rel-field") {
    const rel = internalAvailableRelationships.value.find(
      (r) => r.name === d.relName,
    );
    const field = (rel?.related_fields || []).find(
      (f) => f.name === d.fieldName,
    );
    const relLabel = rel ? (t(rel.label) ?? rel.name) : d.relName;
    const fieldLabel = field ? (t(field.label) ?? field.name) : d.fieldName;
    return `${relLabel} › ${fieldLabel}`;
  }
  if (d.source === "new-section") {
    return sectionTypeLabel(d.sectionType);
  }
  if (d.source === "section-field") {
    const section = internalSections.value[d.sectionIndex];
    const item = section?.items?.[d.itemIndex];
    return item?.kind === "field"
      ? (t(item.label) ?? item.name)
      : (item?.kind ?? "");
  }
  if (d.source === "section-reorder") {
    const section = internalSections.value[d.sectionIndex];
    return section ? sectionTypeLabel(section.type) : "";
  }
  if (d.source === "li-column") {
    const col = internalSections.value[d.sectionIndex]?.columns?.[d.colIndex];
    return col ? (t(col.label) ?? col.name) : "";
  }
  return "";
});

// ── Section management ──────────────────────────────────────────────────────

const hasLineItems = computed(() =>
  internalSections.value.some((s) => s.type === "line_items"),
);

const LI_REQUIRED = new Set(["position", "name", "total"]);

const createSectionOfType = (type) => {
  if (type === "fields")
    return {
      id: `section-fields-${Date.now()}`,
      type: "fields",
      name: "",
      items: [],
    };
  if (type === "text")
    return { id: `section-text-${Date.now()}`, type: "text", content: "" };
  if (type === "divider")
    return { id: `section-divider-${Date.now()}`, type: "divider" };
  if (type === "line_items")
    return {
      id: `section-lineitems-${Date.now()}`,
      type: "line_items",
      columns: [],
    };
  return null;
};

// ── New-section drag (right sidebar) ───────────────────────────────────────

const isNewSectionDragging = (sectionType) =>
  dragging.value?.source === "new-section" &&
  dragging.value.sectionType === sectionType;

const onNewSectionDragStart = (sectionType, event) => {
  beginDrag(
    { source: "new-section", sectionType },
    event,
    ".pdf-editor__available-fields__item",
  );
};

const updateHeaderTitle = (sectionIndex, title) => {
  internalSections.value[sectionIndex].title = title;
  emit("update:sections", internalSections.value);
};

const removeSection = (sectionIndex) => {
  internalSections.value.splice(sectionIndex, 1);
  emit("update:sections", internalSections.value);
};

const updateSectionName = (sectionIndex, name) => {
  internalSections.value[sectionIndex].name = name;
  emit("update:sections", internalSections.value);
};

const updateTextContent = (sectionIndex, content) => {
  internalSections.value[sectionIndex].content = content;
  emit("update:sections", internalSections.value);
};

const removeItemFromSection = (sectionIndex, itemIndex) => {
  internalSections.value[sectionIndex].items.splice(itemIndex, 1);
  emit("update:sections", internalSections.value);
};

const updateFieldItemLabel = (sectionIndex, itemIndex, label) => {
  internalSections.value[sectionIndex].items[itemIndex].label = label;
  emit("update:sections", internalSections.value);
};

// ── Header items ────────────────────────────────────────────────────────────

const addHeaderField = (sectionIndex, field) => {
  const section = internalSections.value[sectionIndex];
  if (!section.items) section.items = [];
  section.items.push({
    kind: "field",
    name: field.name,
    label: field.label,
    type: field.type,
  });
  openPickerIndex.value = null;
  emit("update:sections", internalSections.value);
};

const addHeaderTextItem = (sectionIndex) => {
  const section = internalSections.value[sectionIndex];
  if (!section.items) section.items = [];
  section.items.push({ kind: "text", content: "" });
  emit("update:sections", internalSections.value);
};

const removeHeaderItem = (sectionIndex, itemIndex) => {
  internalSections.value[sectionIndex].items.splice(itemIndex, 1);
  emit("update:sections", internalSections.value);
};

const updateHeaderItemText = (sectionIndex, itemIndex, content) => {
  internalSections.value[sectionIndex].items[itemIndex].content = content;
  emit("update:sections", internalSections.value);
};

// ── Section reorder drag ────────────────────────────────────────────────────

const isDraggingSection = (index) =>
  dragging.value?.source === "section-reorder" &&
  dragging.value.sectionIndex === index;

const isSectionDropZoneActive = (index) =>
  dragOver.value?.target === "section-reorder" &&
  dragOver.value.index === index;

const onSectionDragStart = (sectionIndex, event) => {
  beginDrag({ source: "section-reorder", sectionIndex }, event, ".ple-section");
};

const isBoundaryLocked = (targetIndex) => {
  const sections = internalSections.value;
  if (targetIndex === 0 && sections[0]?.locked) return true;
  if (targetIndex === sections.length && sections[sections.length - 1]?.locked)
    return true;
  return false;
};

const onSectionDragOver = (targetIndex, event) => {
  const src = dragging.value?.source;
  if (src !== "section-reorder" && src !== "new-section") return;
  if (isBoundaryLocked(targetIndex)) return;
  setDragOver({ target: "section-reorder", index: targetIndex }, event);
};

const onSectionDrop = (targetIndex, event) => {
  event.preventDefault();
  if (!dragging.value || isBoundaryLocked(targetIndex)) {
    endDrag();
    return;
  }
  const d = dragging.value;

  if (d.source === "new-section") {
    const section = createSectionOfType(d.sectionType);
    if (section) {
      internalSections.value.splice(targetIndex, 0, section);
      emit("update:sections", internalSections.value);
    }
    endDrag();
    return;
  }

  if (d.source !== "section-reorder") {
    endDrag();
    return;
  }

  const fromIndex = d.sectionIndex;
  if (fromIndex === targetIndex) {
    endDrag();
    return;
  }
  const sections = [...internalSections.value];
  const [item] = sections.splice(fromIndex, 1);
  const insertAt = targetIndex > fromIndex ? targetIndex - 1 : targetIndex;
  sections.splice(insertAt, 0, item);
  internalSections.value = sections;
  emit("update:sections", internalSections.value);
  endDrag();
};

// ── Field drag into section ─────────────────────────────────────────────────

const isFieldItemDragging = (sectionIndex, itemIndex) =>
  dragging.value?.source === "section-field" &&
  dragging.value.sectionIndex === sectionIndex &&
  dragging.value.itemIndex === itemIndex;

const isFieldDropZoneActive = (sectionIndex, itemIndex) =>
  dragOver.value?.target === "section-field" &&
  dragOver.value.sectionIndex === sectionIndex &&
  dragOver.value.itemIndex === itemIndex;

const isSectionEmptyDropActive = (sectionIndex) =>
  dragOver.value?.target === "section-empty" &&
  dragOver.value.sectionIndex === sectionIndex;

const isAvailableDragging = (fieldIndex) =>
  dragging.value?.source === "available" &&
  dragging.value.fieldIndex === fieldIndex;

const onAvailableDragStart = (fieldIndex, event) => {
  beginDrag({ source: "available", fieldIndex }, event, ".ple-available-item");
};

const onFieldItemDragStart = (sectionIndex, itemIndex, event) => {
  beginDrag(
    { source: "section-field", sectionIndex, itemIndex },
    event,
    ".ple-field-item",
  );
};

const onFieldDropZoneDragOver = (sectionIndex, itemIndex, event) => {
  const src = dragging.value?.source;
  if (
    src !== "available" &&
    src !== "section-field" &&
    src !== "available-rel-field"
  )
    return;
  setDragOver({ target: "section-field", sectionIndex, itemIndex }, event);
};

const onSectionEmptyDragOver = (sectionIndex, event) => {
  const src = dragging.value?.source;
  if (
    src !== "available" &&
    src !== "section-field" &&
    src !== "available-rel-field"
  )
    return;
  setDragOver({ target: "section-empty", sectionIndex }, event);
};

const onDropOnSection = (sectionIndex, itemIndex, event) => {
  event.preventDefault();
  if (!dragging.value) return;
  const d = dragging.value;

  if (d.source === "available") {
    const field = filteredAvailableFields.value[d.fieldIndex];
    if (!field) {
      endDrag();
      return;
    }
    const item = {
      kind: "field",
      name: field.name,
      label: field.label,
      type: field.type,
    };
    const targetSection = internalSections.value[sectionIndex];
    if (!targetSection.items) targetSection.items = [];
    targetSection.items.splice(itemIndex, 0, item);
  } else if (d.source === "available-rel-field") {
    const rel = internalAvailableRelationships.value.find(
      (r) => r.name === d.relName,
    );
    const field = (rel?.related_fields || []).find(
      (f) => f.name === d.fieldName,
    );
    if (!field) {
      endDrag();
      return;
    }
    const item = {
      kind: "field",
      name: field.name,
      label: field.label,
      type: field.type,
      relationship: d.relName,
    };
    const targetSection = internalSections.value[sectionIndex];
    if (!targetSection.items) targetSection.items = [];
    targetSection.items.splice(itemIndex, 0, item);
  } else if (d.source === "section-field") {
    const fromSection = d.sectionIndex;
    const fromItem = d.itemIndex;
    if (fromSection === sectionIndex && fromItem === itemIndex) {
      endDrag();
      return;
    }
    const sections = [...internalSections.value];
    const [item] = sections[fromSection].items.splice(fromItem, 1);
    if (!sections[sectionIndex].items) sections[sectionIndex].items = [];
    const toIdx =
      fromSection === sectionIndex && fromItem < itemIndex
        ? itemIndex - 1
        : itemIndex;
    sections[sectionIndex].items.splice(toIdx, 0, item);
    internalSections.value = sections;
  }

  emit("update:sections", internalSections.value);
  endDrag();
};

const onDropOnSectionEmpty = (sectionIndex, event) => {
  onDropOnSection(sectionIndex, 0, event);
};

// Drop on available = remove from section
const onDropOnAvailable = (event) => {
  event.preventDefault();
  if (!dragging.value || dragging.value.source !== "section-field") return;
  const { sectionIndex, itemIndex } = dragging.value;
  internalSections.value[sectionIndex].items.splice(itemIndex, 1);
  emit("update:sections", internalSections.value);
  endDrag();
};

// ── Line-item column reorder ────────────────────────────────────────────────

const isLiColumnDragging = (sectionIndex, colIndex) =>
  dragging.value?.source === "li-column" &&
  dragging.value.sectionIndex === sectionIndex &&
  dragging.value.colIndex === colIndex;

const isLiColumnDropActive = (sectionIndex, zoneIndex) =>
  dragOver.value?.target === "li-column" &&
  dragOver.value.sectionIndex === sectionIndex &&
  dragOver.value.zoneIndex === zoneIndex;

const onLiColumnDragStart = (sectionIndex, colIndex, event) => {
  beginDrag(
    { source: "li-column", sectionIndex, colIndex },
    event,
    ".ple-li-col-item",
  );
};

const onLiColumnDragOver = (sectionIndex, zoneIndex, event) => {
  if (dragging.value?.source === "li-column") {
    setDragOver({ target: "li-column", sectionIndex, zoneIndex }, event);
  }
};

const onLiColumnDrop = (sectionIndex, toZone, event) => {
  event.preventDefault();
  if (!dragging.value || dragging.value.source !== "li-column") return;
  const { sectionIndex: fromSection, colIndex: fromIndex } = dragging.value;
  if (
    fromSection !== sectionIndex ||
    toZone === fromIndex ||
    toZone === fromIndex + 1
  ) {
    endDrag();
    return;
  }
  const cols = [...internalSections.value[sectionIndex].columns];
  const [item] = cols.splice(fromIndex, 1);
  cols.splice(fromIndex < toZone ? toZone - 1 : toZone, 0, item);
  internalSections.value[sectionIndex].columns = cols;
  emit("update:sections", internalSections.value);
  endDrag();
};

const removeLiColumn = (sectionIndex, colIndex) => {
  internalSections.value[sectionIndex].columns.splice(colIndex, 1);
  emit("update:sections", internalSections.value);
};

const liColumnCount = (section) => 3 + (section.columns || []).length;

const getAvailableLiColumns = (section) => {
  const used = new Set((section.columns || []).map((c) => c.name));
  return props.lineItemFields.filter(
    (f) => !LI_REQUIRED.has(f.name) && !used.has(f.name),
  );
};

const addLiColumn = (sectionIndex, field) => {
  const section = internalSections.value[sectionIndex];
  if (!section.columns) section.columns = [];
  section.columns.push({
    name: field.name,
    label: field.label,
    type: field.type,
    enabled: true,
  });
  openLiPickerIndex.value = null;
  emit("update:sections", internalSections.value);
};

// ── Section width (half / full) ─────────────────────────────────────────────

const canBeHalf = (section) => section.type === "fields";

const toggleSectionWidth = (sectionIndex) => {
  const s = internalSections.value[sectionIndex];
  s.width = s.width === "half" ? "full" : "half";
  emit("update:sections", internalSections.value);
};

const sectionRows = computed(() => {
  const rows = [];
  let i = 0;
  const sections = internalSections.value;
  while (i < sections.length) {
    const s = sections[i];
    if (canBeHalf(s) && s.width === "half") {
      const next = sections[i + 1];
      if (next && canBeHalf(next) && next.width === "half") {
        rows.push({
          key: `${s.id}-${next.id}`,
          sections: [
            { section: s, index: i },
            { section: next, index: i + 1 },
          ],
          dropAfterIndex: i + 2,
          halfSolo: false,
        });
        i += 2;
        continue;
      }
    }
    rows.push({
      key: s.id,
      sections: [{ section: s, index: i }],
      dropAfterIndex: i + 1,
      halfSolo: canBeHalf(s) && s.width === "half",
    });
    i++;
  }
  return rows;
});

// ── Half-slot drop zone (second slot in a lone half-width row) ──────────────

const isHalfSlotDropActive = (row) =>
  dragOver.value?.target === "half-slot" && dragOver.value.rowKey === row.key;

const onHalfSlotDragOver = (row, event) => {
  const src = dragging.value?.source;
  if (src === "new-section") {
    if (dragging.value.sectionType !== "fields") return;
  } else if (src === "section-reorder") {
    const section = internalSections.value[dragging.value.sectionIndex];
    if (!canBeHalf(section)) return;
  } else {
    return;
  }
  setDragOver({ target: "half-slot", rowKey: row.key }, event);
};

const onHalfSlotDrop = (row, event) => {
  event.preventDefault();
  if (!dragging.value) return;
  const d = dragging.value;
  const halfIndex = row.sections[0].index;

  if (d.source === "new-section") {
    if (d.sectionType === "fields") {
      const section = createSectionOfType("fields");
      section.width = "half";
      internalSections.value.splice(halfIndex + 1, 0, section);
      emit("update:sections", internalSections.value);
    }
    endDrag();
    return;
  }

  if (d.source !== "section-reorder") {
    endDrag();
    return;
  }

  const fromIndex = d.sectionIndex;
  if (fromIndex === halfIndex) {
    endDrag();
    return;
  }

  const sections = [...internalSections.value];
  const [item] = sections.splice(fromIndex, 1);
  item.width = "half";
  const adjustedHalfIndex = fromIndex < halfIndex ? halfIndex - 1 : halfIndex;
  sections.splice(adjustedHalfIndex + 1, 0, item);
  internalSections.value = sections;
  emit("update:sections", internalSections.value);
  endDrag();
};

// ── Helpers ─────────────────────────────────────────────────────────────────

const sectionTypeLabel = (type) => {
  const map = {
    header: t("layouts.pdf_block_header"),
    footer: t("layouts.pdf_block_footer"),
    fields: t("layouts.pdf_block_fields"),
    text: t("layouts.pdf_block_text"),
    divider: t("layouts.pdf_block_divider"),
    line_items: t("layouts.pdf_block_line_items"),
    relationship: t("layouts.pdf_block_relationship"),
  };
  return map[type] ?? type;
};

const isLocked = (section) => !!section.locked;
const isMovable = (section) => !section.locked;
</script>

<template>
  <div class="pdf-editor" @dragover="onGlobalDragOver">
    <div class="pdf-editor__container">
      <!-- Sidebar: available fields -->
      <div class="pdf-editor__container__sidebar">
        <div
          class="pdf-editor__empty-drop-zone"
          :class="{
            'pdf-editor__empty-drop-zone--active':
              dragOver?.target === 'available-remove',
          }"
          @dragover="setDragOver({ target: 'available-remove' }, $event)"
          @drop="onDropOnAvailable($event)"
        >
          {{ $t("layouts.drop_here_to_remove") }}
        </div>
        <div class="pdf-editor__container__sidebar__content">
          <div class="pdf-editor__container__sidebar__header">
            <span class="pdf-editor__container__sidebar__header__title">
              {{ $t("layouts.available_fields") }}
            </span>
          </div>

          <div class="pdf-editor__available-fields">
            <div
              v-for="(field, index) in filteredAvailableFields"
              :key="field.name"
              class="pdf-editor__available-fields__item ple-available-item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isAvailableDragging(index),
              }"
              draggable="true"
              @dragstart="onAvailableDragStart(index, $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span class="pdf-editor__available-fields__item__label">
                {{ $t(field.label) ?? field.name }}
              </span>
              <span class="pdf-editor__available-fields__item__type">
                {{ $t("fields.types." + field.type) }}
              </span>
            </div>

            <div
              v-if="filteredAvailableFields.length === 0"
              class="pdf-editor__available-fields__no-fields"
            >
              {{ $t("layouts.all_fields_used") }}
            </div>
          </div>
          <!-- Relationships: collapsible groups with per-field drags -->
          <div
            v-for="rel in internalAvailableRelationships"
            :key="rel.name"
            class="ple-rel-group"
          >
            <button
              type="button"
              class="ple-rel-group__header"
              @click="toggleRelExpanded(rel.name)"
            >
              <i
                class="fa-solid"
                :class="
                  expandedRelationships[rel.name]
                    ? 'fa-chevron-down'
                    : 'fa-chevron-right'
                "
              ></i>
              <span class="ple-rel-group__name">{{
                $t(`modules.${rel.related_slug}.label`) ?? rel.name
              }}</span>
              <span class="ple-rel-group__count">{{
                getAvailableRelFields(rel).length
              }}</span>
            </button>
            <div
              v-if="expandedRelationships[rel.name]"
              class="ple-rel-group__body"
            >
              <div class="ple-rel-group__search">
                <i class="fa-solid fa-search ple-rel-group__search-icon"></i>
                <input
                  v-model="relSearchQueries[rel.name]"
                  type="text"
                  class="ple-rel-group__search-input"
                  placeholder="Search fields…"
                />
              </div>
              <div
                v-for="field in getAvailableRelFields(rel)"
                :key="field.name"
                class="pdf-editor__available-fields__item ple-available-item"
                :class="{
                  'pdf-editor__available-fields__item--dragging':
                    isRelFieldDragging(rel.name, field.name),
                }"
                draggable="true"
                @dragstart="onRelFieldDragStart(rel.name, field.name, $event)"
                @dragend="endDrag"
              >
                <span class="pdf-editor__available-fields__item__handle">
                  <i class="fa-solid fa-grip-vertical"></i>
                </span>
                <span class="pdf-editor__available-fields__item__label">
                  {{ $t(field.label) ?? field.name }}
                </span>
                <span class="pdf-editor__available-fields__item__type">
                  {{ $t("fields.types." + field.type) }}
                </span>
              </div>
              <div
                v-if="getAvailableRelFields(rel).length === 0"
                class="pdf-editor__available-fields__no-fields"
              >
                {{ $t("layouts.all_fields_used") }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="pdf-editor__container__main">
        <div class="pdf-editor__container__main__content">
          <div class="pdf-editor__canvas">
            <div
              class="ple-section-drop-zone"
              :class="{
                'ple-section-drop-zone--active': isSectionDropZoneActive(0),
              }"
              @dragover="onSectionDragOver(0, $event)"
              @drop="onSectionDrop(0, $event)"
            />

            <template v-for="row in sectionRows" :key="row.key">
              <div
                class="ple-canvas-row"
                :class="{
                  'ple-canvas-row--pair': row.sections.length === 2,
                  'ple-canvas-row--half-solo': row.halfSolo,
                }"
              >
                <div
                  v-for="{ section, index: sectionIndex } in row.sections"
                  :key="section.id"
                  class="ple-section"
                  :class="{
                    'ple-section--locked': isLocked(section),
                    'ple-section--dragging': isDraggingSection(sectionIndex),
                    [`ple-section--${section.type}`]: true,
                  }"
                >
                  <!-- Section header bar -->
                  <div class="ple-section__header">
                    <span
                      v-if="isMovable(section)"
                      class="ple-section__handle"
                      draggable="true"
                      @dragstart="onSectionDragStart(sectionIndex, $event)"
                      @dragend="endDrag"
                    >
                      <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                    <span
                      v-else
                      class="ple-section__handle ple-section__handle--locked"
                    >
                      <i class="fa-solid fa-lock"></i>
                    </span>

                    <span class="ple-section__type-badge">
                      {{ sectionTypeLabel(section.type) }}
                    </span>

                    <!-- Editable name for fields / relationship sections -->
                    <input
                      v-if="section.type === 'fields'"
                      class="ple-section__name-input"
                      :value="section.name"
                      :placeholder="$t('layouts.pdf_section_name_placeholder')"
                      @input="
                        updateSectionName(sectionIndex, $event.target.value)
                      "
                    />
                    <input
                      v-else-if="section.type === 'relationship'"
                      class="ple-section__name-input"
                      :value="$t(section.label)"
                      :placeholder="$t('layouts.pdf_section_name_placeholder')"
                      @input="
                        internalSections.value[sectionIndex].label =
                          $event.target.value;
                        emit('update:sections', internalSections.value);
                      "
                    />

                    <span
                      v-else-if="section.type === 'header'"
                      class="ple-section__hint"
                    >
                      <i
                        class="fa-solid fa-building"
                        style="margin-right: 4px; opacity: 0.6"
                      ></i>
                      {{ $t("layouts.pdf_header_hint") }}
                    </span>
                    <span
                      v-else-if="section.type === 'footer'"
                      class="ple-section__hint"
                    >
                      {{ $t("layouts.pdf_footer_hint") }}
                    </span>

                    <button
                      v-if="canBeHalf(section)"
                      type="button"
                      class="ple-section__width-toggle"
                      :title="
                        section.width === 'half'
                          ? $t('layouts.expand_to_full')
                          : $t('layouts.shrink_to_half')
                      "
                      @click="toggleSectionWidth(sectionIndex)"
                    >
                      <i
                        :class="
                          section.width === 'half'
                            ? 'fa-solid fa-expand'
                            : 'fa-solid fa-compress'
                        "
                      ></i>
                    </button>
                    <button
                      v-if="isMovable(section) && section.type !== 'divider'"
                      type="button"
                      class="ple-section__remove"
                      @click="removeSection(sectionIndex)"
                    >
                      <i class="fa-solid fa-times"></i>
                    </button>
                  </div>

                  <!-- Section body -->
                  <div class="ple-section__body">
                    <!-- Fields section -->
                    <template v-if="section.type === 'fields'">
                      <div
                        v-if="!section.items?.length"
                        class="pdf-editor__sections__item__content__empty"
                        :class="{
                          'pdf-editor__sections__item__content__empty--active':
                            isSectionEmptyDropActive(sectionIndex),
                        }"
                        @dragover="onSectionEmptyDragOver(sectionIndex, $event)"
                        @drop="onDropOnSectionEmpty(sectionIndex, $event)"
                      >
                        <p>{{ $t("layouts.drop_fields_here") }}</p>
                      </div>
                      <div v-else class="pdf-editor__columns">
                        <!-- Drop zone before first item -->
                        <div
                          class="pdf-editor__columns__drop-zone pdf-editor__columns__drop-zone--horizontal"
                          :class="{
                            'pdf-editor__columns__drop-zone--active':
                              isFieldDropZoneActive(sectionIndex, 0),
                          }"
                          @dragover="
                            onFieldDropZoneDragOver(sectionIndex, 0, $event)
                          "
                          @drop="onDropOnSection(sectionIndex, 0, $event)"
                        />

                        <template
                          v-for="(item, itemIndex) in section.items"
                          :key="itemIndex"
                        >
                          <div
                            class="pdf-editor__columns__item ple-field-item"
                            :class="{
                              'pdf-editor__columns__item--dragging':
                                isFieldItemDragging(sectionIndex, itemIndex),
                            }"
                          >
                            <div
                              class="pdf-editor__columns__item__content"
                              draggable="true"
                              @dragstart="
                                onFieldItemDragStart(
                                  sectionIndex,
                                  itemIndex,
                                  $event,
                                )
                              "
                              @dragend="endDrag"
                            >
                              <span class="pdf-editor__columns__item__handle">
                                <i class="fa-solid fa-grip-vertical"></i>
                              </span>
                              <span class="pdf-editor__columns__item__label">
                                <template v-if="item.relationship">
                                  <input
                                    class="ple-label-input"
                                    :value="t(item.label)"
                                    @change="
                                      updateFieldItemLabel(
                                        sectionIndex,
                                        itemIndex,
                                        $event.target.value,
                                      )
                                    "
                                    @click.stop
                                    @mousedown.stop
                                  />
                                  <span class="ple-rel-badge">{{
                                    item.relationship
                                  }}</span>
                                </template>
                                <template v-else>
                                  {{ $t(item.label) ?? item.name }}
                                </template>
                              </span>
                              <button
                                type="button"
                                class="pdf-editor__columns__item__remove"
                                :title="$t('layouts.remove_column')"
                                @click="
                                  removeItemFromSection(sectionIndex, itemIndex)
                                "
                              >
                                <i class="fa-solid fa-times"></i>
                              </button>
                            </div>
                            <!-- Drop zone after item -->
                            <div
                              class="pdf-editor__columns__drop-zone pdf-editor__columns__drop-zone--horizontal"
                              :class="{
                                'pdf-editor__columns__drop-zone--active':
                                  isFieldDropZoneActive(
                                    sectionIndex,
                                    itemIndex + 1,
                                  ),
                              }"
                              @dragover="
                                onFieldDropZoneDragOver(
                                  sectionIndex,
                                  itemIndex + 1,
                                  $event,
                                )
                              "
                              @drop="
                                onDropOnSection(
                                  sectionIndex,
                                  itemIndex + 1,
                                  $event,
                                )
                              "
                            />
                          </div>
                        </template>
                      </div>
                    </template>

                    <!-- Line items section -->
                    <template v-else-if="section.type === 'line_items'">
                      <!-- Column configurator -->
                      <div class="ple-li-cols">
                        <div class="ple-li-cols__row">
                          <!-- position: required, always first -->
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf_li_position")
                            }}</span>
                          </div>
                          <!-- name: required, always second -->
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf.name")
                            }}</span>
                          </div>

                          <!-- drop zone before first optional column -->
                          <div
                            class="ple-li-chip-drop"
                            :class="{
                              'ple-li-chip-drop--active': isLiColumnDropActive(
                                sectionIndex,
                                0,
                              ),
                            }"
                            @dragover="
                              onLiColumnDragOver(sectionIndex, 0, $event)
                            "
                            @drop="onLiColumnDrop(sectionIndex, 0, $event)"
                          />

                          <template
                            v-for="(col, colIndex) in section.columns"
                            :key="col.name"
                          >
                            <div
                              class="ple-li-chip"
                              :class="{
                                'ple-li-chip--dragging': isLiColumnDragging(
                                  sectionIndex,
                                  colIndex,
                                ),
                              }"
                            >
                              <i
                                class="fa-solid fa-grip-vertical ple-li-chip__handle"
                                draggable="true"
                                @dragstart="
                                  onLiColumnDragStart(
                                    sectionIndex,
                                    colIndex,
                                    $event,
                                  )
                                "
                                @dragend="endDrag"
                              ></i>
                              <span class="ple-li-chip__label">{{
                                $t(col.label) ?? col.name
                              }}</span>
                              <button
                                type="button"
                                class="ple-li-chip__remove"
                                @click="removeLiColumn(sectionIndex, colIndex)"
                              >
                                <i class="fa-solid fa-times"></i>
                              </button>
                            </div>
                            <div
                              class="ple-li-chip-drop"
                              :class="{
                                'ple-li-chip-drop--active':
                                  isLiColumnDropActive(
                                    sectionIndex,
                                    colIndex + 1,
                                  ),
                              }"
                              @dragover="
                                onLiColumnDragOver(
                                  sectionIndex,
                                  colIndex + 1,
                                  $event,
                                )
                              "
                              @drop="
                                onLiColumnDrop(
                                  sectionIndex,
                                  colIndex + 1,
                                  $event,
                                )
                              "
                            />
                          </template>

                          <!-- total: required, always last -->
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf.total")
                            }}</span>
                          </div>
                        </div>

                        <!-- add column picker -->
                        <div
                          v-if="getAvailableLiColumns(section).length"
                          class="ple-li-add-col"
                        >
                          <button
                            type="button"
                            class="ple-li-add-col__btn"
                            @click="
                              openLiPickerIndex =
                                openLiPickerIndex === sectionIndex
                                  ? null
                                  : sectionIndex
                            "
                          >
                            <i class="fa-solid fa-plus"></i>
                            {{ $t("layouts.add_column") }}
                          </button>
                          <div
                            v-if="openLiPickerIndex === sectionIndex"
                            class="ple-li-add-col__dropdown"
                          >
                            <button
                              v-for="field in getAvailableLiColumns(section)"
                              :key="field.name"
                              type="button"
                              class="ple-li-add-col__option"
                              @click="addLiColumn(sectionIndex, field)"
                            >
                              <span class="ple-li-add-col__option-label">{{
                                $t(field.label) ?? field.name
                              }}</span>
                              <span class="ple-li-add-col__option-type">{{
                                $t("fields.types." + field.type)
                              }}</span>
                            </button>
                          </div>
                        </div>

                        <!-- column count hint / warning -->
                        <div
                          class="ple-li-col-hint"
                          :class="{
                            'ple-li-col-hint--warn':
                              liColumnCount(section) >= 8,
                          }"
                        >
                          <i
                            :class="
                              liColumnCount(section) >= 8
                                ? 'fa-solid fa-triangle-exclamation'
                                : 'fa-solid fa-circle-info'
                            "
                          ></i>
                          {{ liColumnCount(section) }}
                          {{ $t("layouts.pdf_li_col_count") }}
                          <template v-if="liColumnCount(section) < 8">
                            — {{ $t("layouts.pdf_li_col_recommended") }}
                          </template>
                          <template v-else>
                            — {{ $t("layouts.pdf_li_col_over") }}
                          </template>
                        </div>
                      </div>

                      <!-- Dynamic preview strip -->
                      <div class="ple__li-preview">
                        <div
                          class="ple__li-preview__row ple__li-preview__row--head"
                        >
                          <span class="ple__li-preview__pos">#</span>
                          <span class="ple__li-preview__name">{{
                            $t("layouts.pdf.name")
                          }}</span>
                          <span
                            v-for="col in section.columns || []"
                            :key="col.name"
                            class="ple__li-preview__col"
                            >{{ $t(col.label) ?? col.name }}</span
                          >
                          <span class="ple__li-preview__col">{{
                            $t("layouts.pdf.total")
                          }}</span>
                        </div>
                        <div
                          v-for="i in 2"
                          :key="i"
                          class="ple__li-preview__row"
                        >
                          <span class="ple__li-preview__pos">{{ i }}</span>
                          <span class="ple__li-preview__name"
                            ><span
                              class="ple__placeholder-bar"
                              style="width: 70%; height: 8px"
                            ></span
                          ></span>
                          <span
                            v-for="col in section.columns || []"
                            :key="col.name"
                            class="ple__li-preview__col"
                            ><span
                              class="ple__placeholder-bar"
                              style="width: 55%; height: 8px"
                            ></span
                          ></span>
                          <span class="ple__li-preview__col"
                            ><span
                              class="ple__placeholder-bar"
                              style="width: 55%; height: 8px"
                            ></span
                          ></span>
                        </div>
                      </div>

                      <!-- Totals -->
                      <div class="ple__totals">
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.subtotal")
                          }}</span>
                          <span
                            class="ple__placeholder-bar"
                            style="width: 70px"
                          ></span>
                        </div>
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.discount_amount")
                          }}</span>
                          <span
                            class="ple__placeholder-bar"
                            style="width: 70px"
                          ></span>
                        </div>
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.tax_amount")
                          }}</span>
                          <span
                            class="ple__placeholder-bar"
                            style="width: 60px"
                          ></span>
                        </div>
                        <div class="ple__totals__row ple__totals__row--grand">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.total")
                          }}</span>
                          <span
                            class="ple__placeholder-bar"
                            style="width: 80px"
                          ></span>
                        </div>
                      </div>
                    </template>

                    <!-- Text block -->
                    <template v-else-if="section.type === 'text'">
                      <textarea
                        class="ple-text-input"
                        :value="section.content"
                        :placeholder="$t('layouts.pdf_text_placeholder')"
                        rows="3"
                        @input="
                          updateTextContent(sectionIndex, $event.target.value)
                        "
                      />
                    </template>

                    <!-- Divider -->
                    <template v-else-if="section.type === 'divider'">
                      <div class="ple-divider-row">
                        <hr class="ple-divider-preview" />
                        <button
                          type="button"
                          class="ple-section__remove"
                          @click="removeSection(sectionIndex)"
                        >
                          <i class="fa-solid fa-times"></i>
                        </button>
                      </div>
                    </template>

                    <!-- Header preview -->
                    <template v-else-if="section.type === 'header'">
                      <div class="ple-header-preview">
                        <div class="ple-header-preview__left">
                          <div class="ple-header-preview__logo">
                            <img
                              v-if="company.company_logo_url"
                              :src="company.company_logo_url"
                              class="ple-header-preview__logo-img"
                            />
                            <div
                              v-else
                              class="ple-header-preview__logo-initials"
                            >
                              {{ companyInitials }}
                            </div>
                          </div>
                          <div class="ple-header-preview__company">
                            <div class="ple-header-preview__company-name">
                              {{ company.company_name || "—" }}
                            </div>
                            <div class="ple-header-preview__company-meta">
                              <div v-if="company.company_address">
                                {{ company.company_address }}
                              </div>
                              <div
                                v-if="
                                  company.company_phone || company.company_email
                                "
                              >
                                {{
                                  [company.company_phone, company.company_email]
                                    .filter(Boolean)
                                    .join(" · ")
                                }}
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="ple-header-preview__right">
                          <!-- Document title (editable) -->
                          <input
                            class="ple-header-preview__title-input"
                            :value="section.title || moduleLabel"
                            :placeholder="moduleLabel || 'Document Title'"
                            @input="
                              updateHeaderTitle(
                                sectionIndex,
                                $event.target.value,
                              )
                            "
                          />

                          <!-- Configurable meta items -->
                          <div
                            v-if="section.items?.length"
                            class="ple-header-items"
                          >
                            <div
                              v-for="(item, itemIndex) in section.items"
                              :key="itemIndex"
                              class="ple-header-item"
                            >
                              <template v-if="item.kind === 'field'">
                                <span class="ple-header-item__key">{{
                                  $t(item.label) ?? item.name
                                }}</span>
                                <span class="ple-header-item__sep">—</span>
                                <button
                                  type="button"
                                  class="ple-header-item__remove"
                                  @click="
                                    removeHeaderItem(sectionIndex, itemIndex)
                                  "
                                >
                                  <i class="fa-solid fa-times"></i>
                                </button>
                              </template>
                              <template v-else-if="item.kind === 'text'">
                                <input
                                  class="ple-header-item__text-input"
                                  :value="item.content"
                                  :placeholder="
                                    $t('layouts.pdf_text_placeholder')
                                  "
                                  @input="
                                    updateHeaderItemText(
                                      sectionIndex,
                                      itemIndex,
                                      $event.target.value,
                                    )
                                  "
                                />
                                <button
                                  type="button"
                                  class="ple-header-item__remove"
                                  @click="
                                    removeHeaderItem(sectionIndex, itemIndex)
                                  "
                                >
                                  <i class="fa-solid fa-times"></i>
                                </button>
                              </template>
                            </div>
                          </div>

                          <!-- Drop target for dragged sidebar fields -->
                          <div
                            class="ple-header-drop"
                            :class="{
                              'ple-header-drop--active': isFieldDropZoneActive(
                                sectionIndex,
                                (section.items || []).length,
                              ),
                            }"
                            @dragover="
                              onFieldDropZoneDragOver(
                                sectionIndex,
                                (section.items || []).length,
                                $event,
                              )
                            "
                            @drop="
                              onDropOnSection(
                                sectionIndex,
                                (section.items || []).length,
                                $event,
                              )
                            "
                          />

                          <!-- Add buttons -->
                          <div class="ple-header-add-row">
                            <div class="ple-li-add-col">
                              <button
                                type="button"
                                class="ple-li-add-col__btn"
                                @click="
                                  openPickerIndex =
                                    openPickerIndex === sectionIndex
                                      ? null
                                      : sectionIndex
                                "
                              >
                                <i class="fa-solid fa-plus"></i>
                                {{ $t("layouts.add_column") }}
                              </button>
                              <div
                                v-if="openPickerIndex === sectionIndex"
                                class="ple-li-add-col__dropdown"
                              >
                                <button
                                  v-for="field in filteredAvailableFields"
                                  :key="field.name"
                                  type="button"
                                  class="ple-li-add-col__option"
                                  @click="addHeaderField(sectionIndex, field)"
                                >
                                  <span class="ple-li-add-col__option-label">{{
                                    $t(field.label) ?? field.name
                                  }}</span>
                                  <span class="ple-li-add-col__option-type">{{
                                    $t("fields.types." + field.type)
                                  }}</span>
                                </button>
                                <div
                                  v-if="!filteredAvailableFields.length"
                                  style="
                                    padding: 8px 12px;
                                    color: #9ca3af;
                                    font-size: 12px;
                                  "
                                >
                                  {{ $t("layouts.all_fields_used") }}
                                </div>
                              </div>
                            </div>
                            <button
                              type="button"
                              class="ple-header-add-text-btn"
                              @click="addHeaderTextItem(sectionIndex)"
                            >
                              <i class="fa-solid fa-font"></i>
                              {{ $t("layouts.text_block") }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- Legacy relationship section -->
                    <template v-else-if="section.type === 'relationship'">
                      <p class="ple-locked-hint" style="color: #b45309">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Legacy section — remove and use related fields from the
                        sidebar instead.
                      </p>
                    </template>

                    <!-- Footer hint -->
                    <template v-else-if="section.type === 'footer'">
                      <p class="ple-locked-hint">
                        {{ $t("layouts.pdf_footer_hint") }}
                      </p>
                    </template>
                  </div>
                </div>
                <!-- Half-slot: second slot when this row has a lone half-width section -->
                <div
                  v-if="row.halfSolo"
                  class="ple-half-slot-drop"
                  :class="{
                    'ple-half-slot-drop--active': isHalfSlotDropActive(row),
                  }"
                  @dragover="onHalfSlotDragOver(row, $event)"
                  @drop="onHalfSlotDrop(row, $event)"
                />
              </div>

              <!-- Drop zone after each row -->
              <div
                class="ple-section-drop-zone"
                :class="{
                  'ple-section-drop-zone--active': isSectionDropZoneActive(
                    row.dropAfterIndex,
                  ),
                }"
                @dragover="onSectionDragOver(row.dropAfterIndex, $event)"
                @drop="onSectionDrop(row.dropAfterIndex, $event)"
              />
            </template>

            <div v-if="internalSections.length === 0" class="ple-empty-canvas">
              {{ $t("layouts.pdf_sections") }}
            </div>
          </div>
        </div>
      </div>

      <!-- Right sidebar: draggable section types -->
      <div
        class="pdf-editor__container__sidebar pdf-editor__container__sidebar--right"
      >
        <div class="pdf-editor__container__sidebar__content">
          <div class="pdf-editor__container__sidebar__header">
            <span class="pdf-editor__container__sidebar__header__title">
              {{ $t("layouts.pdf_sections") }}
            </span>
          </div>
          <div class="pdf-editor__available-fields">
            <div
              v-for="item in [
                {
                  type: 'fields',
                  icon: 'fa-table-columns',
                  labelKey: 'field_section',
                },
                { type: 'text', icon: 'fa-align-left', labelKey: 'text_block' },
                { type: 'divider', icon: 'fa-minus', labelKey: 'divider' },
              ]"
              :key="item.type"
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isNewSectionDragging(item.type),
              }"
              draggable="true"
              @dragstart="onNewSectionDragStart(item.type, $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>

              <span class="pdf-editor__available-fields__item__label">{{
                $t(`layouts.${item.labelKey}`)
              }}</span>
              <span class="pdf-editor__available-fields__item__type">
                <i :class="`fa-solid ${item.icon} `"></i>
              </span>
            </div>
            <div
              v-if="!hasLineItems"
              class="pdf-editor__available-fields__item"
              :class="{
                'ple-new-section-item--dragging':
                  isNewSectionDragging('line_items'),
              }"
              draggable="true"
              @dragstart="onNewSectionDragStart('line_items', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>

              <span class="pdf-editor__available-fields__item__label">{{
                $t("layouts.line_items")
              }}</span>
              <span class="pdf-editor__available-fields__item__type">
                <i class="fa-solid fa-table-list"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="dragging"
      class="pdf-editor__ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
        width: ghostWidth || 'auto',
        height: ghostHeight || 'auto',
      }"
    >
      <span class="pdf-editor__ghost__handle">
        <i class="fa-solid fa-grip-vertical"></i>
      </span>
      <span class="pdf-editor__ghost__label">{{ ghostLabel }}</span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.pdf-editor {
  overflow: hidden;
  border-radius: 8px;
  user-select: none;

  &__available-fields {
    min-height: 250px;
    margin-bottom: 2rem;

    &__item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 12px;
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      margin-bottom: 8px;
      cursor: move;
      transition: all 0.2s;
      user-select: none;

      &:hover {
        border-color: #adb5bd;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      }
      &--dragging {
        opacity: 0.5;
        background: #f8f9fa;
      }
      &__handle {
        color: #adb5bd;
        cursor: move;
        &:active {
          cursor: movebing;
        }
      }
      &__label {
        font-size: 0.9rem;
        color: #212529;
        flex: 1;
      }
      &__type {
        border: 1px solid var(--secondary-color);
        padding: 2.5px;
        font-size: 0.7rem;
        border-radius: 5px;
        color: var(--secondary-color);
        background-color: color-mix(
          in srgb,
          var(--secondary-color) 10%,
          rgb(255, 255, 255)
        );
      }
    }
    &__no-fields {
      text-align: center;
      padding: 20px;
      color: #6c757d;
      font-size: 14px;
      background: white;
      border: 1px dashed #dee2e6;
      border-radius: 6px;
    }
  }

  &__empty-drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 12px;
    transition: all 0.2s;
    &--active {
      border-color: #dc3545;
      background-color: rgba(220, 53, 69, 0.05);
      color: #dc3545;
    }
  }

  &__container {
    display: flex;
    padding: 20px;
    gap: 24px;

    &__sidebar {
      width: 220px;
      flex-shrink: 0;

      &--right {
        width: 180px;
      }

      &__content {
        padding: 5px;
        border-radius: 8px;
        min-height: 350px;
        max-height: calc(100vh - 380px);
        position: relative;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
        z-index: 11;
        &::-webkit-scrollbar {
          width: 6px;
        }
        &::-webkit-scrollbar-track {
          background: transparent;
        }
        &::-webkit-scrollbar-thumb {
          background-color: rgba(0, 0, 0, 0.25);
          border-radius: 8px;
        }
      }
      &__header {
        display: flex;
        flex-direction: column;
        margin-bottom: 8px;
        padding: 4px 6px;
        &__title {
          font-family: "Fira Sans", "Heebo", sans-serif;
          font-weight: 700;
          font-size: 1.1rem;
          color: #323450;
        }
      }
    }

    &__main {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-width: 0;

      &__content {
        display: flex;
        flex-direction: column;
        gap: 0;
        width: 100%;
        min-height: 400px;
        height: 100%;
        max-height: calc(100vh - 380px);
        position: relative;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.25) transparent;
        z-index: 11;
        &::-webkit-scrollbar {
          height: 10px;
          width: 10px;
        }
        &::-webkit-scrollbar-track {
          background: transparent;
        }
        &::-webkit-scrollbar-thumb {
          background-color: rgba(0, 0, 0, 0.25);
          border-radius: 8px;
          &:hover {
            background-color: color-mix(
              in srgb,
              var(--module-color) 55%,
              rgba(0, 0, 0, 0.35)
            );
          }
        }
      }
    }
  }

  &__sections__item__content__empty {
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 40px 20px;
    text-align: center;
    transition: all 0.2s;
    color: #6c757d;
    &--active {
      border-color: #0d6efd;
      background-color: rgba(13, 110, 253, 0.05);
    }
  }

  &__columns {
    position: relative;

    &__drop-zone {
      height: 4px;
      margin: 4px 0;
      background: transparent;
      border-radius: 2px;
      transition: all 0.2s;
      &--horizontal {
        width: 100%;
      }
      &--active {
        background: rgba(80, 161, 255, 0.06);
        border: 1px dashed rgba(0, 0, 0, 0.699);
        height: 46px;
        margin: 12px 0;
      }
    }

    &__item {
      position: relative;

      &__content {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 8px;
        cursor: move;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
        @media (max-width: 1250px) {
          padding: 3px;
        }
        &:hover {
          border-color: #adb5bd;
          box-shadow: 0 18px 18px rgba(0, 0, 0, 0.05);
        }
      }
      &--dragging &__content {
        opacity: 0.5;
        background: #f8f9fa;
      }
      &__handle {
        color: #adb5bd;
        cursor: move;
        &:active {
          cursor: movebing;
        }
      }
      &__label {
        flex: 1;
        font-weight: 500;
        color: #212529;
        font-size: 1rem;
        display: flex;
        justify-content: space-between;
        padding-right: 12px;
        @media (max-width: 1250px) {
          font-size: 0.8rem;
        }
      }
      &__remove {
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
        &:hover {
          color: #dc3545;
          background: rgba(220, 53, 69, 0.1);
        }
      }
    }
  }

  &__ghost {
    position: fixed;
    z-index: 100;
    pointer-events: none;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px 12px;
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
      font-size: 14px;
      color: #212529;
    }
  }

  &__canvas {
    max-width: 740px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    box-shadow:
      0 2px 8px rgba(0, 0, 0, 0.08),
      0 0 0 1px rgba(0, 0, 0, 0.04);
    padding: 40px 48px;
  }
}

.ple-section {
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 6px;
  margin-bottom: 4px;
  background: var(--card-bg, #fff);
  transition:
    opacity 0.15s,
    border-color 0.15s;

  &:hover {
    border-color: #adb5bd;
  }
}

.ple-section--dragging {
  opacity: 0.35;
}

.ple-section__header {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  min-height: 28px;
  border-bottom: 1px solid var(--border-color, #e5e7eb);
  background: var(--surface-subtle, #f9fafb);
  border-radius: 6px 6px 0 0;
}

// Drag handle & remove fade in on hover
.ple-section__handle {
  cursor: move;
  color: var(--text-muted, #9ca3af);
  flex-shrink: 0;
  opacity: 0;
  transition: opacity 0.15s;
  &:active {
    cursor: grabbing;
  }
  .ple-section:hover & {
    opacity: 1;
  }
}

.ple-section__handle--locked {
  cursor: default;
  color: var(--text-muted, #c4c9d1);
  opacity: 1 !important;
}

.ple-section__remove {
  opacity: 0;
  transition: opacity 0.15s;
  .ple-section:hover & {
    opacity: 1;
  }
}

.ple-section__type-badge {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #9ca3af;
  background: rgba(0, 0, 0, 0.05);
  padding: 1px 5px;
  border-radius: 3px;
  flex-shrink: 0;
}

// ── Divider — single-line: header row IS the divider ────────────────────────
.ple-section--divider {
  display: flex;
  align-items: center;
}

.ple-section--divider .ple-section__header {
  border-bottom: none;
  background: transparent;
  border-radius: 6px;
  flex-shrink: 0;
  min-height: 28px;
  padding: 4px 8px;
}

.ple-section--divider .ple-section__body {
  flex: 1;
  padding: 0 8px 0 0;
}

.ple-section--divider .ple-divider-preview {
  margin: 0;
}

.ple-section__name-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 13px;
  font-weight: 500;
  outline: none;
  min-width: 0;
}

.ple-section__hint {
  flex: 1;
  font-size: 12px;
  color: var(--text-muted, #9ca3af);
  font-style: italic;
}

.ple-canvas-row {
  &--pair {
    display: flex;
    gap: 8px;

    .ple-section {
      flex: 1;
      min-width: 0;
    }
  }

  &--half-solo {
    display: flex;
    align-items: flex-start;
    gap: 8px;

    .ple-section {
      flex: 0 0 calc(50% - 4px);
      max-width: calc(50% - 4px);
      min-width: 0;
    }
  }
}

.ple-half-slot-drop {
  flex: 1;
  min-height: 48px;
  border-radius: 6px;
  border: 2px dashed #e5e7eb;
  transition: all 0.15s;
  align-self: stretch;

  &--active {
    border-color: var(--primary-color, #6366f1);
    background: color-mix(
      in srgb,
      var(--primary-color, #6366f1) 8%,
      transparent
    );
  }
}

.ple-section__width-toggle {
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted, #9ca3af);
  padding: 2px 5px;
  border-radius: 3px;
  font-size: 11px;
  flex-shrink: 0;
  transition: all 0.15s;

  &:hover {
    color: var(--primary-color, #6366f1);
    background: color-mix(
      in srgb,
      var(--primary-color, #6366f1) 8%,
      transparent
    );
  }
}

.ple-section__remove {
  margin-left: auto;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-muted, #9ca3af);
  padding: 2px 4px;
  flex-shrink: 0;
  &:hover {
    color: var(--danger-color, #ef4444);
  }
}

.ple-section__body {
  padding: 5px;
}

.ple-text-input {
  width: 100%;
  border: 1px solid var(--border-color, #e5e7eb);
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 13px;
  font-family: inherit;
  line-height: 1.6;
  color: #374151;
  resize: vertical;
  background: #fafafa;
  transition:
    border-color 0.15s,
    box-shadow 0.15s;
  min-height: 80px;

  &:hover {
    border-color: #adb5bd;
  }

  &:focus {
    outline: none;
    border-color: var(--primary-color, #6366f1);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--primary-color, #6366f1) 12%, transparent);
    background: #fff;
  }

  &::placeholder {
    color: #c5c9d0;
  }
}

.ple-divider-row {
  display: flex;
  align-items: center;
  gap: 8px;

  .ple-divider-preview {
    flex: 1;
    margin: 0;
  }
}

.ple-divider-preview {
  border: none;
  border-top: 1px solid var(--border-color, #e5e7eb);
  margin: 4px 0;
}

.ple-header-preview {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;

  &__left {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
    min-width: 0;
  }

  &__logo {
    flex-shrink: 0;
    width: 46px;
    height: 46px;
    border-radius: 8px;
    overflow: hidden;
  }

  &__logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  &__logo-initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-color, #6366f1);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    border-radius: 8px;
  }

  &__company {
    flex: 1;
    min-width: 0;
  }

  &__company-name {
    font-size: 13px;
    font-weight: 700;
    color: #111;
    margin-bottom: 3px;
  }

  &__company-meta {
    font-size: 10px;
    color: #666;
    line-height: 1.7;
  }

  &__right {
    flex-shrink: 0;
    text-align: right;
    min-width: 130px;
  }

  &__title-input {
    display: block;
    width: 100%;
    text-align: right;
    font-size: 18px;
    font-weight: 700;
    color: #111;
    border: none;
    border-bottom: 2px solid transparent;
    background: transparent;
    outline: none;
    padding: 1px 0 2px;
    transition: border-color 0.15s;
    font-family: inherit;

    &:hover,
    &:focus {
      border-bottom-color: var(--primary-color, #6366f1);
    }

    &::placeholder {
      color: #c5c9d0;
      font-weight: 400;
    }
  }

  &__doc-number {
    font-size: 10px;
    color: #888;
    text-align: right;
    margin-top: 3px;
  }
}

.ple-locked-hint {
  font-size: 12px;
  color: var(--text-muted, #9ca3af);
  font-style: italic;
  margin: 0;
}

// ── Header editable items ───────────────────────────────────────────────────

.ple-header-items {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.ple-header-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  min-height: 22px;

  &__key {
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    font-size: 11px;
  }

  &__sep {
    color: #d1d5db;
    font-size: 10px;
    flex: 1;
  }

  &__remove {
    background: none;
    border: none;
    cursor: pointer;
    color: #c4c9d1;
    padding: 1px 3px;
    font-size: 10px;
    flex-shrink: 0;
    line-height: 1;
    &:hover {
      color: #ef4444;
    }
  }

  &__text-input {
    flex: 1;
    border: none;
    border-bottom: 1px dashed #e5e7eb;
    background: transparent;
    font-size: 11px;
    font-family: inherit;
    color: #374151;
    padding: 1px 2px;
    outline: none;
    text-align: right;

    &:focus {
      border-bottom-color: var(--primary-color, #6366f1);
    }
    &::placeholder {
      color: #d1d5db;
    }
  }
}

.ple-header-drop {
  height: 24px;
  border-radius: 4px;
  border: 1.5px dashed #e9ecef;
  margin-top: 8px;
  transition: all 0.15s;

  &--active {
    border-color: var(--primary-color, #6366f1);
    background: color-mix(
      in srgb,
      var(--primary-color, #6366f1) 8%,
      transparent
    );
  }
}

.ple-header-add-row {
  display: flex;
  gap: 6px;
  margin-top: 6px;
  align-items: flex-start;
  flex-wrap: wrap;
}

.ple-header-add-text-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  background: none;
  border: 1px dashed #d1d5db;
  border-radius: 5px;
  padding: 5px 10px;
  font-size: 12px;
  color: #6b7280;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s;

  &:hover {
    border-color: var(--primary-color, #6366f1);
    color: var(--primary-color, #6366f1);
    background: color-mix(in srgb, var(--primary-color, #6366f1) 4%, #fff);
  }
}

.ple-section-drop-zone {
  height: 6px;
  border-radius: 3px;
  transition:
    height 0.15s,
    background 0.15s;
  margin: 2px 0;
}

.ple-section-drop-zone--active {
  height: 28px;
  background: color-mix(
    in srgb,
    var(--primary-color, #6366f1) 15%,
    transparent
  );
  border: 2px dashed var(--primary-color, #6366f1);
}

.ple-empty-canvas {
  padding: 40px;
  text-align: center;
  color: var(--text-muted, #9ca3af);
  border: 2px dashed var(--border-color, #e5e7eb);
  border-radius: 6px;
}

.ple__items-grid {
  display: flex;
  flex-direction: column;
  width: 100%;
  font-size: 11px;
}

.ple__items-grid__row {
  display: grid;
  grid-template-columns: 5% 23% 23% 23% 23% 23%;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
  padding: 7px 0;

  &--head {
    border-top: 1.5px solid #111;
    border-bottom: 1.5px solid #111;
    padding: 5px 0;
    span {
      font-size: 10px;
      font-weight: 700;
      color: #111;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
  }

  span {
    margin: 0 5px;
  }
}

.ple__section__type-badge--accent {
  background: color-mix(in srgb, var(--primary-color, #6366f1) 12%, #e5e7eb);
  color: var(--primary-color, #6366f1);
}

.ple__items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;

  thead tr {
    border-top: 1.5px solid #111;
    border-bottom: 1.5px solid #111;
  }

  thead th {
    padding: 5px 6px;
    font-size: 10px;
    font-weight: 700;
    color: #111;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.4px;

    &.col-num {
      text-align: right;
    }
  }

  tbody tr {
    border-bottom: 1px solid #f0f0f0;
  }

  tbody td {
    padding: 7px 6px;
    vertical-align: middle;
    color: #333;

    &.col-num {
      text-align: right;
    }
  }

  .col-pos {
    width: 4%;
  }
  .col-name {
    width: 30%;
  }

  .col-num {
    width: 10%;
  }
}

.ple__items-table__placeholder-row td {
  color: #555;
}

.ple__placeholder-bar {
  display: inline-block;
  height: 9px;
  background: #e5e7eb;
  border-radius: 3px;
  vertical-align: middle;
}

.ple__totals {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-top: 10px;
  gap: 0;
}

.ple__totals__row {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  width: 300px;
  border-bottom: 1px solid #f0f0f0;

  &:last-child {
    border-bottom: none;
  }
}

.ple__totals__row--grand {
  border-top: 1.5px solid #111;
  border-bottom: none;
  margin-top: 4px;
  padding-top: 6px;

  .ple__totals__label,
  .ple__totals__value {
    font-weight: 700;
    font-size: 12px;
    color: #111;
  }
}

.ple__totals__label {
  font-size: 11px;
  color: #666;
}

.ple__totals__value {
  font-size: 11px;
  font-weight: 600;
  color: #111;
}

// ── Line-item column configurator ───────────────────────────────────────────

.ple-li-cols {
  margin-bottom: 10px;

  &__row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0;
    margin-bottom: 8px;
  }
}

.ple-li-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  max-width: 150px;
  padding: 4px 8px;
  border-radius: 5px;
  border: 1px solid #e9ecef;
  background: #f8f9fa;
  font-size: 12px;
  transition: opacity 0.15s;
  margin: 2px 0;

  &--required {
    background: color-mix(in srgb, var(--primary-color, #6366f1) 4%, #f9fafb);
    border-color: color-mix(
      in srgb,
      var(--primary-color, #6366f1) 20%,
      #e5e7eb
    );
    cursor: default;
  }

  &--dragging {
    opacity: 0.4;
  }

  &__lock {
    color: #9ca3af;
    font-size: 10px;
    flex-shrink: 0;
  }

  &__handle {
    color: #adb5bd;
    cursor: move;
    font-size: 11px;
    flex-shrink: 0;
    &:hover {
      color: #6b7280;
    }
  }

  &__label {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
    color: #374151;
  }

  &__remove {
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 0 1px;
    font-size: 10px;
    flex-shrink: 0;
    line-height: 1;
    &:hover {
      color: #ef4444;
    }
  }
}

.ple-li-chip-drop {
  width: 4px;
  height: 26px;
  flex-shrink: 0;
  border-radius: 2px;
  transition: all 0.15s;
  margin: 2px 0;

  &--active {
    width: 18px;
    background: color-mix(
      in srgb,
      var(--primary-color, #6366f1) 12%,
      transparent
    );
    border: 1.5px dashed var(--primary-color, #6366f1);
    border-radius: 4px;
  }
}

.ple-li-add-col {
  position: relative;

  &__btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: 1px dashed #d1d5db;
    border-radius: 5px;
    padding: 5px 12px;
    font-size: 12px;
    color: #6b7280;
    cursor: pointer;
    width: 100%;
    transition: all 0.15s;

    &:hover {
      border-color: var(--primary-color, #6366f1);
      color: var(--primary-color, #6366f1);
      background: color-mix(in srgb, var(--primary-color, #6366f1) 4%, #fff);
    }
  }

  &__dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 20;
    overflow: hidden;
  }

  &__option {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    font-size: 13px;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.1s;

    &:last-child {
      border-bottom: none;
    }

    &:hover {
      background: color-mix(in srgb, var(--primary-color, #6366f1) 6%, #fff);
    }
  }

  &__option-label {
    flex: 1;
    font-weight: 500;
    color: #374151;
  }

  &__option-type {
    font-size: 11px;
    color: var(--secondary-color, #6b7280);
    border: 1px solid currentColor;
    padding: 1px 5px;
    border-radius: 3px;
    opacity: 0.8;
  }
}

.ple-li-col-hint {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 6px;
  font-size: 11px;
  color: #9ca3af;
  padding: 0 2px;

  i {
    font-size: 11px;
  }

  &--warn {
    color: #d97706;
    i {
      color: #d97706;
    }
  }
}

// ── Line-item preview strip ──────────────────────────────────────────────────

.ple__li-preview {
  display: flex;
  flex-direction: column;
  width: 100%;
  font-size: 11px;
  margin-bottom: 8px;

  &__row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
    padding: 5px 0;

    &--head {
      border-top: 1.5px solid #111;
      border-bottom: 1.5px solid #111;
      padding: 4px 0;

      > span {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #111;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
  }

  &__pos {
    flex: 0 0 5%;
    margin: 0 4px;
  }

  &__name {
    flex: 3;
    margin: 0 4px;
    min-width: 0;
  }

  &__col {
    flex: 1;
    margin: 0 4px;
    min-width: 0;
  }
}

// ── Relationship sidebar groups ────────────────────────────────────────────
.ple-rel-group {
  border-bottom: 1px solid var(--border-color, #e5e7eb);

  &__header {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.12s;

    &:hover {
      background: color-mix(
        in srgb,
        var(--primary-color, #6366f1) 5%,
        transparent
      );
    }

    i {
      font-size: 10px;
      color: #9ca3af;
      flex-shrink: 0;
    }
  }

  &__name {
    flex: 1;
  }

  &__count {
    font-size: 10px;
    font-weight: 500;
    color: #9ca3af;
    background: #f3f4f6;
    border-radius: 10px;
    padding: 1px 6px;
    flex-shrink: 0;
  }

  &__body {
    padding: 0 4px 6px;
  }

  &__search {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px 6px;
  }

  &__search-icon {
    font-size: 11px;
    color: #9ca3af;
    flex-shrink: 0;
  }

  &__search-input {
    flex: 1;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    outline: none;
    background: #fafafa;

    &:focus {
      border-color: var(--primary-color, #6366f1);
    }
  }
}

// ── Right sidebar: draggable new-section items ────────────────────────────
.ple-new-section-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.ple-new-section-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 10px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: grab;
  user-select: none;
  transition:
    border-color 0.12s,
    box-shadow 0.12s;

  &:hover {
    border-color: var(--primary-color, #6366f1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
  }

  &--dragging {
    opacity: 0.45;
  }

  &__handle {
    color: #d1d5db;
    font-size: 11px;
    flex-shrink: 0;
  }

  &__icon {
    color: var(--primary-color, #6366f1);
    font-size: 13px;
    flex-shrink: 0;
    width: 14px;
    text-align: center;
  }

  &__label {
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    flex: 1;
    min-width: 0;
  }
}

// ── Editable label input for related field items ───────────────────────────
.ple-label-input {
  background: transparent;
  border: none;
  border-bottom: 1px dashed #d1d5db;
  outline: none;
  font-size: inherit;
  color: #374151;
  width: 100%;
  min-width: 60px;
  padding: 1px 2px;
  cursor: text;
  transition: border-color 0.12s;

  &:hover {
    border-bottom-color: #9ca3af;
  }

  &:focus {
    border-bottom-color: var(--primary-color, #6366f1);
    border-bottom-style: solid;
  }
}

// ── Relationship field badge on canvas items ───────────────────────────────
.ple-rel-badge {
  display: inline-block;
  margin-left: 5px;
  padding: 1px 5px;
  font-size: 10px;
  font-weight: 500;
  border-radius: 3px;
  background: color-mix(in srgb, var(--primary-color, #6366f1) 12%, #e5e7eb);
  color: var(--primary-color, #6366f1);
  vertical-align: middle;
  line-height: 1.4;
  white-space: nowrap;
}
</style>
