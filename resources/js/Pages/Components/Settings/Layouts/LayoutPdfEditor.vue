<script setup>
// ============================================================
// 1. IMPORTS & DEPENDENCIES
// ============================================================
import { ref, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useLayoutDragDrop } from "@/Composables/useLayoutDragDrop";
import ExplainTip from "@/Pages/Components/Globals/ExplainTip.vue";

// ============================================================
// 2. PROPS & EMITS
// ============================================================
const props = defineProps({
  sections: { type: Array, default: () => [] },
  availableFields: { type: Array, default: () => [] },
  availableRelationships: { type: Array, default: () => [] },
  lineItemFields: { type: Array, default: () => [] },
  moduleLabel: { type: String, default: "" },
  module: { type: Object, default: () => {} },
});

const emit = defineEmits(["update:sections"]);

// ============================================================
// 3. GLOBAL HELPERS & TRANSLATION
// ============================================================
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();

// ============================================================
// 4. REACTIVE STATE (REFS)
// ============================================================
const internalSections = ref(cloneSections(props.sections));
const internalAvailable = ref([...props.availableFields]);
const internalAvailableRelationships = ref([...props.availableRelationships]);
const openPickerIndex = ref(null);
const openLiPickerIndex = ref(null);

// Relationship picker state
const expandedRelationships = ref({});
const relSearchQueries = ref({});

// ============================================================
// 5. WATCHERS (Dependencies: props → internal refs)
// ============================================================
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

// ============================================================
// 6. HELPER FUNCTIONS
// ============================================================
function cloneSections(sections) {
  return (sections || []).map((s) => {
    const cloned = { ...s };
    if (s.type === "fields")
      cloned.items = (s.items || []).map((i) => ({ ...i }));
    if (s.type === "header" || s.type === "footer") {
      const cloneItem = (i) => (i ? { ...i } : null);
      if (s.rows) {
        cloned.rows = s.rows.map((r) => ({
          left: cloneItem(r.left),
          right: cloneItem(r.right),
        }));
      } else {
        // migrate header from leftItems/rightItems or original items array
        const leftItems = s.leftItems || [];
        const rightItems = s.rightItems ?? s.items ?? [];
        const len = Math.max(leftItems.length, rightItems.length, 1);
        cloned.rows = Array.from({ length: len }, (_, i) => ({
          left: leftItems[i] ? { ...leftItems[i] } : null,
          right: rightItems[i] ? { ...rightItems[i] } : null,
        }));
      }
    }
    if (s.type === "relationship")
      cloned.columns = (s.columns || []).map((c) => ({ ...c }));
    if (s.type === "line_items")
      cloned.columns = (s.columns || []).map((c) => ({ ...c }));
    return cloned;
  });
}

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

// ============================================================
// 7. DRAG & DROP COMPOSABLE (Dependencies: useLayoutDragDrop)
// ============================================================
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

// ============================================================
// 8. COMPUTED PROPERTIES
// ============================================================
// Company info
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
const today = computed(() => new Date().toLocaleDateString());

// Track used fields to filter available ones
const usedFieldNames = computed(() => {
  const used = new Set();
  internalSections.value.forEach((section) => {
    if (section.type === "fields") {
      (section.items || []).forEach((item) => {
        if (item.kind === "field" && !item.relationship) used.add(item.name);
      });
    }
    if (section.type === "header" || section.type === "footer") {
      (section.rows || []).forEach((row) => {
        [row.left, row.right].filter(Boolean).forEach((item) => {
          if (item.kind === "field" && !item.relationship) used.add(item.name);
        });
      });
    }
  });
  return used;
});

const filteredAvailableFields = computed(() =>
  internalAvailable.value.filter((f) => !usedFieldNames.value.has(f.name)),
);

// Track relationship fields
const usedRelFieldKeys = computed(() => {
  const used = new Set();
  internalSections.value.forEach((section) => {
    if (section.type === "fields") {
      (section.items || []).forEach((item) => {
        if (item.kind === "field" && item.relationship)
          used.add(`${item.relationship}:${item.name}`);
      });
    }
    if (section.type === "header" || section.type === "footer") {
      (section.rows || []).forEach((row) => {
        [row.left, row.right].filter(Boolean).forEach((item) => {
          if (item.kind === "field" && item.relationship)
            used.add(`${item.relationship}:${item.name}`);
        });
      });
    }
  });
  return used;
});

// Line items validation
const hasLineItems = computed(() =>
  internalSections.value.some((s) => s.type === "line_items"),
);

const moduleHasLineItems = computed(() => props.module?.has_line_items === 1);
const LI_REQUIRED = new Set(["position", "name", "total"]);

// Section layout (half/full width)
const canBeHalf = (section) => section.type === "fields";

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

// ============================================================
// 9. DRAG GHOST LABEL (Visual feedback during drag)
// ============================================================
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
    return `${fieldLabel}`;
  }
  if (d.source === "new-section") {
    return sectionTypeLabel(d.sectionType);
  }
  if (d.source === "new-header-block") {
    const labels = {
      logo: "Logo",
      meta: t("layouts.meta_block"),
      title: t("layouts.title_block"),
      page_number: t("layouts.page_number_block"),
      date: t("layouts.date_block"),
      co_info_line: t("layouts.co_info_line_block"),
    };
    return labels[d.blockType] || d.blockType;
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

// ============================================================
// 10. RELATIONSHIP FIELD PICKER (Sidebar)
// ============================================================
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

// ============================================================
// 11. SECTION MANAGEMENT (Create, Remove, Update)
// ============================================================
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

const toggleSectionWidth = (sectionIndex) => {
  const s = internalSections.value[sectionIndex];
  s.width = s.width === "half" ? "full" : "half";
  emit("update:sections", internalSections.value);
};

// ============================================================
// 12. NEW SECTION DRAG (Right Sidebar)
// ============================================================
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

// ============================================================
// 13. FIELD ITEMS MANAGEMENT (Fields section)
// ============================================================
const removeItemFromSection = (sectionIndex, itemIndex) => {
  internalSections.value[sectionIndex].items.splice(itemIndex, 1);
  emit("update:sections", internalSections.value);
};

const updateFieldItemLabel = (sectionIndex, itemIndex, label) => {
  internalSections.value[sectionIndex].items[itemIndex].label = label;
  emit("update:sections", internalSections.value);
};

const toggleFieldLabel = (sectionIndex, itemIndex) => {
  const item = internalSections.value[sectionIndex].items[itemIndex];
  item.showLabel = item.showLabel === false ? true : false;
  emit("update:sections", internalSections.value);
};

const updateFieldItemStyle = (sectionIndex, itemIndex, style) => {
  const item = internalSections.value[sectionIndex]?.items?.[itemIndex];
  if (!item) return;
  if (style) item.displayStyle = style;
  else delete item.displayStyle;
  emit("update:sections", internalSections.value);
};

// ============================================================
// 14. HEADER/FOOTER MANAGEMENT
// ============================================================
const updateHeaderTitle = (sectionIndex, title) => {
  internalSections.value[sectionIndex].title = title;
  emit("update:sections", internalSections.value);
};

const removeHeaderSlotItem = (sectionIndex, rowIndex, side) => {
  const rows = internalSections.value[sectionIndex]?.rows;
  if (rows?.[rowIndex]) {
    rows[rowIndex][side] = null;
    emit("update:sections", internalSections.value);
  }
};

const addHeaderRow = (sectionIndex) => {
  const section = internalSections.value[sectionIndex];
  if (!section.rows) section.rows = [];
  section.rows.push({ left: null, right: null });
  emit("update:sections", internalSections.value);
};

const removeHeaderRow = (sectionIndex, rowIndex) => {
  const rows = internalSections.value[sectionIndex]?.rows;
  if (rows && rows.length > 1) {
    rows.splice(rowIndex, 1);
    emit("update:sections", internalSections.value);
  }
};

// ============================================================
// 15. HEADER BUILDING BLOCKS (Right Sidebar)
// ============================================================
const onHeaderBlockDragStart = (blockType, event) => {
  beginDrag(
    { source: "new-header-block", blockType },
    event,
    ".pdf-editor__available-fields__item",
  );
};

const isHeaderBlockDragging = (blockType) =>
  dragging.value?.source === "new-header-block" &&
  dragging.value.blockType === blockType;

// ============================================================
// 16. HEADER SLOT DROP ZONES
// ============================================================
const isHeaderSlotDropActive = (sectionIndex, rowIndex, side) =>
  dragOver.value?.target === "header-slot" &&
  dragOver.value.sectionIndex === sectionIndex &&
  dragOver.value.rowIndex === rowIndex &&
  dragOver.value.side === side;

const onHeaderSlotDragOver = (sectionIndex, rowIndex, side, event) => {
  const src = dragging.value?.source;
  if (
    src !== "available" &&
    src !== "available-rel-field" &&
    src !== "new-header-block"
  )
    return;
  const row = (internalSections.value[sectionIndex]?.rows || [])[rowIndex];
  if (!row || row[side] != null) return;
  setDragOver({ target: "header-slot", sectionIndex, rowIndex, side }, event);
};

const onDropOnHeaderSlot = (sectionIndex, rowIndex, side, event) => {
  event.preventDefault();
  if (!dragging.value) return;
  const d = dragging.value;
  const section = internalSections.value[sectionIndex];
  if (!section.rows) section.rows = [{ left: null, right: null }];
  const row = section.rows[rowIndex];
  if (!row || row[side] != null) {
    endDrag();
    return;
  }

  let item = null;

  if (d.source === "available") {
    const field = filteredAvailableFields.value[d.fieldIndex];
    if (!field) {
      endDrag();
      return;
    }
    item = {
      kind: "field",
      name: field.name,
      label: field.label,
      type: field.type,
    };
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
    item = {
      kind: "field",
      name: field.name,
      label: field.label,
      type: field.type,
      relationship: d.relName,
    };
  } else if (d.source === "new-header-block") {
    if (
      ["logo", "title", "page_number", "date", "co_info_line"].includes(
        d.blockType,
      )
    ) {
      const exists = (section.rows || []).some(
        (r) => r.left?.kind === d.blockType || r.right?.kind === d.blockType,
      );
      if (exists) {
        endDrag();
        return;
      }
    }
    item = { kind: d.blockType };
  }

  if (item) {
    row[side] = item;
    emit("update:sections", internalSections.value);
  }
  endDrag();
};

// ============================================================
// 17. SECTION REORDER DRAG & DROP
// ============================================================
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

// ============================================================
// 18. FIELD DRAG INTO SECTION (Available fields → Section)
// ============================================================
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
    src !== "available-rel-field" &&
    src !== "new-header-block"
  )
    return;
  setDragOver({ target: "section-field", sectionIndex, itemIndex }, event);
};

const onSectionEmptyDragOver = (sectionIndex, event) => {
  const src = dragging.value?.source;
  if (
    src !== "available" &&
    src !== "section-field" &&
    src !== "available-rel-field" &&
    src !== "new-header-block"
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
      showLabel: true,
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
      showLabel: true,
    };
    const targetSection = internalSections.value[sectionIndex];
    if (!targetSection.items) targetSection.items = [];
    targetSection.items.splice(itemIndex, 0, item);
  } else if (d.source === "new-header-block") {
    const targetSection = internalSections.value[sectionIndex];
    if (!targetSection.items) targetSection.items = [];
    targetSection.items.splice(itemIndex, 0, { kind: d.blockType });
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

// ============================================================
// 19. LINE ITEMS MANAGEMENT
// ============================================================
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

const removeLiColumn = (sectionIndex, colIndex) => {
  internalSections.value[sectionIndex].columns.splice(colIndex, 1);
  emit("update:sections", internalSections.value);
};

const liColumnCount = (section) => 3 + (section.columns || []).length;

// ============================================================
// 20. LINE ITEMS COLUMN REORDER
// ============================================================
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

// ============================================================
// 21. HALF-WIDTH SLOT DROP ZONE (Lone half-width row)
// ============================================================
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
</script>
<template>
  <div class="pdf-editor" @dragover="onGlobalDragOver">
    <div class="pdf-editor__container">
      <div class="pdf-editor__container__sidebar">
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
                  :placeholder="t('settings.search_in_drop_down')"
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
                  <div class="ple-section__body">
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
                              <template v-if="item.kind === 'field'">
                                <span
                                  class="pdf-editor__columns__item__label"
                                  :class="
                                    item.displayStyle
                                      ? `ple-item-style--${item.displayStyle}`
                                      : ''
                                  "
                                >
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
                                  </template>
                                  <template v-else>
                                    {{ $t(item.label) ?? item.name }}
                                  </template>
                                </span>
                                <select
                                  class="ple-field-style-select"
                                  :class="{
                                    'ple-field-style-select--active':
                                      item.displayStyle,
                                  }"
                                  :value="item.displayStyle || ''"
                                  @change="
                                    updateFieldItemStyle(
                                      sectionIndex,
                                      itemIndex,
                                      $event.target.value,
                                    )
                                  "
                                  @click.stop
                                  @mousedown.stop
                                >
                                  <option value="">
                                    {{ $t("layouts.display_style_none") }}
                                  </option>
                                  <option value="title">
                                    {{ $t("layouts.display_style_title") }}
                                  </option>
                                  <option value="subtitle">
                                    {{ $t("layouts.display_style_subtitle") }}
                                  </option>
                                  <option value="bold">
                                    {{ $t("layouts.display_style_bold") }}
                                  </option>
                                  <option value="small">
                                    {{ $t("layouts.display_style_small") }}
                                  </option>
                                  <option value="label">
                                    {{ $t("layouts.display_style_label") }}
                                  </option>
                                  <option value="status">
                                    {{ $t("layouts.display_style_status") }}
                                  </option>
                                  <option value="address">
                                    {{ $t("layouts.display_style_address") }}
                                  </option>
                                  <option value="highlight">
                                    {{ $t("layouts.display_style_highlight") }}
                                  </option>
                                  <option value="muted">
                                    {{ $t("layouts.display_style_muted") }}
                                  </option>
                                </select>
                                <div
                                  class="ple-field-label-toggle"
                                  @click.stop
                                  @mousedown.stop
                                >
                                  <label class="ple-field-label-toggle__check">
                                    <input
                                      type="checkbox"
                                      :checked="item.showLabel !== false"
                                      @change="
                                        toggleFieldLabel(
                                          sectionIndex,
                                          itemIndex,
                                        )
                                      "
                                    />
                                    <span>{{ $t("layouts.show_label") }}</span>
                                  </label>
                                  <ExplainTip
                                    :text="$t('layouts.tip_show_label')"
                                  />
                                </div>
                              </template>
                              <template v-else>
                                <span
                                  class="pdf-editor__columns__item__label ple-bb-chip"
                                >
                                  <i
                                    class="fa-solid"
                                    :class="{
                                      'fa-image': item.kind === 'logo',
                                      'fa-building': item.kind === 'meta',
                                      'fa-heading': item.kind === 'title',
                                      'fa-hashtag': item.kind === 'page_number',
                                      'fa-calendar': item.kind === 'date',
                                      'fa-address-card':
                                        item.kind === 'co_info_line',
                                    }"
                                  ></i>
                                  {{ $t(`layouts.${item.kind}_block`) }}
                                </span>
                              </template>
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
                    <template v-else-if="section.type === 'line_items'">
                      <div class="ple-li-cols">
                        <div class="ple-li-cols__row">
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf_li_position")
                            }}</span>
                          </div>
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf.name")
                            }}</span>
                          </div>
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
                          <div class="ple-li-chip ple-li-chip--required">
                            <i class="fa-solid fa-lock ple-li-chip__lock"></i>
                            <span class="ple-li-chip__label">{{
                              $t("layouts.pdf.total")
                            }}</span>
                          </div>
                        </div>
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
                            - {{ $t("layouts.pdf_li_col_recommended") }}
                          </template>
                          <template v-else>
                            - {{ $t("layouts.pdf_li_col_over") }}
                          </template>
                        </div>
                      </div>
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
                              class="ple__placeholder-bar ple__placeholder-bar--row-name"
                            ></span
                          ></span>
                          <span
                            v-for="col in section.columns || []"
                            :key="col.name"
                            class="ple__li-preview__col"
                            ><span
                              class="ple__placeholder-bar ple__placeholder-bar--row-cell"
                            ></span
                          ></span>
                          <span class="ple__li-preview__col"
                            ><span
                              class="ple__placeholder-bar ple__placeholder-bar--row-cell"
                            ></span
                          ></span>
                        </div>
                      </div>
                      <div class="ple__totals">
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.subtotal")
                          }}</span>
                          <span
                            class="ple__placeholder-bar ple__placeholder-bar--total-md"
                          ></span>
                        </div>
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.discount_amount")
                          }}</span>
                          <span
                            class="ple__placeholder-bar ple__placeholder-bar--total-md"
                          ></span>
                        </div>
                        <div class="ple__totals__row">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.tax_amount")
                          }}</span>
                          <span
                            class="ple__placeholder-bar ple__placeholder-bar--total-sm"
                          ></span>
                        </div>
                        <div class="ple__totals__row ple__totals__row--grand">
                          <span class="ple__totals__label">{{
                            $t("layouts.pdf.total")
                          }}</span>
                          <span
                            class="ple__placeholder-bar ple__placeholder-bar--total-lg"
                          ></span>
                        </div>
                      </div>
                    </template>
                    <template v-else-if="section.type === 'text'">
                      <textarea
                        class="ple-text-input"
                        :value="section.content"
                        :placeholder="$t('layouts.pdf_text_placeholder')"
                        @input="
                          updateTextContent(sectionIndex, $event.target.value)
                        "
                      />
                    </template>
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
                    <template
                      v-else-if="
                        section.type === 'header' || section.type === 'footer'
                      "
                    >
                      <div class="ple-header-rows">
                        <div
                          v-for="(row, rowIndex) in section.rows || [
                            { left: null, right: null },
                          ]"
                          :key="rowIndex"
                          class="ple-header-row"
                        >
                          <!-- Left slot -->
                          <div class="ple-header-slot">
                            <div
                              v-if="row.left == null"
                              class="ple-header-slot__empty"
                              :class="{
                                'ple-header-slot__empty--active':
                                  isHeaderSlotDropActive(
                                    sectionIndex,
                                    rowIndex,
                                    'left',
                                  ),
                              }"
                              @dragover="
                                onHeaderSlotDragOver(
                                  sectionIndex,
                                  rowIndex,
                                  'left',
                                  $event,
                                )
                              "
                              @drop="
                                onDropOnHeaderSlot(
                                  sectionIndex,
                                  rowIndex,
                                  'left',
                                  $event,
                                )
                              "
                            >
                              <span>{{ $t("layouts.drop_boxes_here") }}</span>
                            </div>
                            <div v-else class="ple-hb-item">
                              <template v-if="row.left.kind === 'logo'">
                                <div class="ple-hb-logo">
                                  <img
                                    v-if="company.company_logo_url"
                                    :src="company.company_logo_url"
                                    class="ple-hb-logo__img"
                                  />
                                  <div v-else class="ple-hb-logo__initials">
                                    {{ companyInitials }}
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.left.kind === 'meta'">
                                <div class="ple-hb-meta">
                                  <div class="ple-hb-meta__name">
                                    {{ company.company_name }}
                                  </div>
                                  <div
                                    v-if="company.company_address"
                                    class="ple-hb-meta__address"
                                  >
                                    {{ company.company_address }}
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.left.kind === 'title'">
                                <div class="ple-hb-title">
                                  <div class="ple-header-title-wrap">
                                    <input
                                      class="ple-header-preview__title-input"
                                      :value="section.title"
                                      :placeholder="
                                        moduleLabel || 'Document Title'
                                      "
                                      @input="
                                        updateHeaderTitle(
                                          sectionIndex,
                                          $event.target.value,
                                        )
                                      "
                                    />
                                    <button
                                      v-if="section.title"
                                      type="button"
                                      class="ple-header-title-clear"
                                      :title="$t('layouts.clear_title')"
                                      @click="
                                        updateHeaderTitle(sectionIndex, '')
                                      "
                                    >
                                      <i class="fa-solid fa-times"></i>
                                    </button>
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.left.kind === 'field'">
                                <span class="ple-hb-field">
                                  {{ $t(row.left.label) ?? row.left.name }}
                                </span>
                              </template>
                              <template
                                v-else-if="row.left.kind === 'page_number'"
                              >
                                <span class="ple-hb-page-number">1 / 3</span>
                              </template>
                              <template v-else-if="row.left.kind === 'date'">
                                <span class="ple-hb-date">{{ today }}</span>
                              </template>
                              <template
                                v-else-if="row.left.kind === 'co_info_line'"
                              >
                                <div class="ple-hb-co-info-line">
                                  {{
                                    [
                                      company.company_name,
                                      company.company_address,
                                      company.company_phone,
                                      company.company_email,
                                    ]
                                      .filter(Boolean)
                                      .join(" · ") ||
                                    $t("layouts.co_info_line_block")
                                  }}
                                </div>
                              </template>
                              <button
                                type="button"
                                class="ple-hb-item__remove"
                                @click="
                                  removeHeaderSlotItem(
                                    sectionIndex,
                                    rowIndex,
                                    'left',
                                  )
                                "
                              >
                                <i class="fa-solid fa-times"></i>
                              </button>
                            </div>
                          </div>

                          <!-- Right slot -->
                          <div class="ple-header-slot">
                            <div
                              v-if="row.right == null"
                              class="ple-header-slot__empty"
                              :class="{
                                'ple-header-slot__empty--active':
                                  isHeaderSlotDropActive(
                                    sectionIndex,
                                    rowIndex,
                                    'right',
                                  ),
                              }"
                              @dragover="
                                onHeaderSlotDragOver(
                                  sectionIndex,
                                  rowIndex,
                                  'right',
                                  $event,
                                )
                              "
                              @drop="
                                onDropOnHeaderSlot(
                                  sectionIndex,
                                  rowIndex,
                                  'right',
                                  $event,
                                )
                              "
                            >
                              <span>{{ $t("layouts.drop_boxes_here") }}</span>
                            </div>
                            <div v-else class="ple-hb-item">
                              <template v-if="row.right.kind === 'logo'">
                                <div class="ple-hb-logo">
                                  <img
                                    v-if="company.company_logo_url"
                                    :src="company.company_logo_url"
                                    class="ple-hb-logo__img"
                                  />
                                  <div v-else class="ple-hb-logo__initials">
                                    {{ companyInitials }}
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.right.kind === 'meta'">
                                <div class="ple-hb-meta">
                                  <div class="ple-hb-meta__name">
                                    {{ company.company_name }}
                                  </div>
                                  <div
                                    v-if="company.company_address"
                                    class="ple-hb-meta__address"
                                  >
                                    {{ company.company_address }}
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.right.kind === 'title'">
                                <div class="ple-hb-title">
                                  <div class="ple-header-title-wrap">
                                    <input
                                      class="ple-header-preview__title-input"
                                      :value="section.title"
                                      :placeholder="
                                        moduleLabel || 'Document Title'
                                      "
                                      @input="
                                        updateHeaderTitle(
                                          sectionIndex,
                                          $event.target.value,
                                        )
                                      "
                                    />
                                    <button
                                      v-if="section.title"
                                      type="button"
                                      class="ple-header-title-clear"
                                      :title="$t('layouts.clear_title')"
                                      @click="
                                        updateHeaderTitle(sectionIndex, '')
                                      "
                                    >
                                      <i class="fa-solid fa-times"></i>
                                    </button>
                                  </div>
                                </div>
                              </template>
                              <template v-else-if="row.right.kind === 'field'">
                                <span class="ple-hb-field">
                                  {{ $t(row.right.label) ?? row.right.name }}
                                </span>
                              </template>
                              <template
                                v-else-if="row.right.kind === 'page_number'"
                              >
                                <span class="ple-hb-page-number">1 / 3</span>
                              </template>
                              <template v-else-if="row.right.kind === 'date'">
                                <span class="ple-hb-date">{{ today }}</span>
                              </template>
                              <template
                                v-else-if="row.right.kind === 'co_info_line'"
                              >
                                <div class="ple-hb-co-info-line">
                                  {{
                                    [
                                      company.company_name,
                                      company.company_address,
                                      company.company_phone,
                                      company.company_email,
                                    ]
                                      .filter(Boolean)
                                      .join(" · ") ||
                                    $t("layouts.co_info_line_block")
                                  }}
                                </div>
                              </template>
                              <button
                                type="button"
                                class="ple-hb-item__remove"
                                @click="
                                  removeHeaderSlotItem(
                                    sectionIndex,
                                    rowIndex,
                                    'right',
                                  )
                                "
                              >
                                <i class="fa-solid fa-times"></i>
                              </button>
                            </div>
                          </div>

                          <!-- Remove row -->
                          <button
                            v-if="(section.rows || []).length > 1"
                            type="button"
                            class="ple-header-row__remove"
                            @click="removeHeaderRow(sectionIndex, rowIndex)"
                          >
                            <i class="fa-solid fa-times"></i>
                          </button>
                        </div>

                        <button
                          v-if="section.type !== 'footer'"
                          type="button"
                          class="ple-header-add-row-btn"
                          @click="addHeaderRow(sectionIndex)"
                        >
                          <i class="fa-solid fa-plus"></i>
                          {{ $t("layouts.add_row") }}
                        </button>
                      </div>
                    </template>
                  </div>
                </div>
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
                  tipKey: 'tip_block_fields',
                },
                {
                  type: 'text',
                  icon: 'fa-align-left',
                  labelKey: 'text_block',
                  tipKey: 'tip_block_text',
                },
                {
                  type: 'divider',
                  icon: 'fa-minus',
                  labelKey: 'divider',
                  tipKey: 'tip_block_divider',
                },
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
              <ExplainTip :text="$t(`layouts.${item.tipKey}`)" />
            </div>
            <div
              v-if="!hasLineItems && moduleHasLineItems"
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
              <ExplainTip :text="$t('layouts.tip_block_line_items')" />
            </div>
          </div>

          <div class="pdf-editor__available-fields">
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('logo'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('logo', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <img
                  v-if="company.company_logo_url"
                  :src="company.company_logo_url"
                  class="ple-bb-logo-thumb"
                />
                <i v-else class="fa-solid fa-image ple-bb-icon"></i>
                Logo
              </span>
              <ExplainTip :text="$t('layouts.tip_logo')" />
            </div>
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('meta'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('meta', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <i class="fa-solid fa-building ple-bb-icon"></i>
                {{ $t("layouts.meta_block") }}
              </span>
              <ExplainTip :text="$t('layouts.tip_meta')" />
            </div>
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('title'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('title', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <i class="fa-solid fa-heading ple-bb-icon"></i>
                {{ $t("layouts.title_block") }}
              </span>
              <ExplainTip :text="$t('layouts.tip_title_block')" />
            </div>
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('page_number'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('page_number', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <i class="fa-solid fa-file-lines ple-bb-icon"></i>
                {{ $t("layouts.page_number_block") }}
              </span>
              <ExplainTip :text="$t('layouts.tip_page_number')" />
            </div>
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('date'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('date', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <i class="fa-solid fa-calendar ple-bb-icon"></i>
                {{ $t("layouts.date_block") }}
              </span>
              <ExplainTip :text="$t('layouts.tip_date')" />
            </div>
            <div
              class="pdf-editor__available-fields__item"
              :class="{
                'pdf-editor__available-fields__item--dragging':
                  isHeaderBlockDragging('co_info_line'),
              }"
              draggable="true"
              @dragstart="onHeaderBlockDragStart('co_info_line', $event)"
              @dragend="endDrag"
            >
              <span class="pdf-editor__available-fields__item__handle">
                <i class="fa-solid fa-grip-vertical"></i>
              </span>
              <span
                class="pdf-editor__available-fields__item__label ple-bb-label"
              >
                <i class="fa-solid fa-address-card ple-bb-icon"></i>
                {{ $t("layouts.co_info_line_block") }}
              </span>
              <ExplainTip :text="$t('layouts.tip_co_info_line')" />
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
