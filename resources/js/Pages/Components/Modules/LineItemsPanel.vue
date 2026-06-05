<script setup>
import { ref, computed, watch, getCurrentInstance, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import RecordSelectorDrawer from "@/Pages/Components/Modules/RecordSelectorDrawer.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";

const props = defineProps({
  parentId: { type: String, required: true },
  parentType: { type: String, required: true },
  mode: { type: String, default: "detail" },
  currency: { type: String, default: "" },
  moduleColor: { type: String, default: "var(--module-color)" },
  productFields: { type: Array, default: () => [] },
  lineItemFields: { type: Array, default: () => [] },
});

const emit = defineEmits(["totals-updated"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const { error, clearAllAlerts, success } = useAlerts();
const { confirm } = useConfirm();

const rowErrors = ref({});

const validateRow = () => {
  const errors = {};
  const v = row.value;

  if (!v.name?.trim()) {
    errors.name = "modules.line_items.errors.name_required";
  }

  const up =
    v.unit_price === "" || v.unit_price === null || v.unit_price === undefined;
  if (up) {
    errors.unit_price = "modules.line_items.errors.unit_price_required";
  } else if (isNaN(parseFloat(v.unit_price)) || parseFloat(v.unit_price) < 0) {
    errors.unit_price = "modules.line_items.errors.numeric_min_zero";
  }

  const qty =
    v.quantity === "" || v.quantity === null || v.quantity === undefined;
  if (qty) {
    errors.quantity = "modules.line_items.errors.quantity_required";
  } else if (isNaN(parseFloat(v.quantity)) || parseFloat(v.quantity) < 0) {
    errors.quantity = "modules.line_items.errors.numeric_min_zero";
  }

  if (v.discount !== "" && v.discount !== null && v.discount !== undefined) {
    const d = parseFloat(v.discount);
    if (isNaN(d) || d < 0) {
      errors.discount = "modules.line_items.errors.numeric_min_zero";
    } else if (d > 100) {
      errors.discount = "modules.line_items.errors.discount_max";
    }
  }

  if (v.tax_rate !== "" && v.tax_rate !== null && v.tax_rate !== undefined) {
    const tr = parseFloat(v.tax_rate);
    if (isNaN(tr) || tr < 0) {
      errors.tax_rate = "modules.line_items.errors.numeric_min_zero";
    }
  }

  if (v.unit && v.unit.length > 255) {
    errors.unit = "modules.line_items.errors.unit_max";
  }

  if (v.note && v.note.length > 1000) {
    errors.note = "modules.line_items.errors.note_max";
  }

  rowErrors.value = errors;
  return Object.keys(errors).length === 0;
};

// ── State ─────────────────────────────────────────────────────────────────────
const items = ref([]);
const loading = ref(false);
const saving = ref(false);
const drawerOpen = ref(false);
const editingItem = ref(null); // null = new row, object = existing row
// ── Fetch ─────────────────────────────────────────────────────────────────────

const fetchItems = async () => {
  loading.value = true;
  try {
    const { data } = await axios.get(`/line-items`, {
      params: { parent_type: props.parentType, parent_id: props.parentId },
    });
    items.value = data;
  } catch (e) {
    console.error("LineItemsPanel fetch error", e.message);
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.parentId,
  (val) => {
    if (val) fetchItems();
  },
  { immediate: true },
);

// ── Totals ────────────────────────────────────────────────────────────────────

const totals = computed(() => {
  const subtotal = items.value.reduce(
    (s, i) => s + parseFloat(i.subtotal || 0),
    0,
  );
  const discount_amount = items.value.reduce(
    (s, i) => s + parseFloat(i.discount_amount || 0),
    0,
  );
  const tax_amount = items.value.reduce(
    (s, i) => s + parseFloat(i.tax_amount || 0),
    0,
  );
  const total = items.value.reduce((s, i) => s + parseFloat(i.total || 0), 0);
  return { subtotal, discount_amount, tax_amount, total };
});

watch(totals, (val) => emit("totals-updated", val));

const fmt = (n) =>
  Number(n).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

// ── Row form state (used in the drawer/sheet) ─────────────────────────────────

const emptyRow = () => ({
  id: null,
  product_id: null,
  product__label: "",
  name: "",
  unit: "",
  unit_price: "",
  quantity: 1,
  discount: 0,
  tax_rate: 0,
  note: "",
  // computed — shown read-only in the form
  subtotal: 0,
  discount_amount: 0,
  tax_amount: 0,
  total: 0,
});

const getLineItemField = (name) =>
  props.lineItemFields.find((f) => f.name === name) ?? { name, type: "text" };

const row = ref(emptyRow());

const recalcRow = () => {
  const up = parseFloat(row.value.unit_price || 0);
  const qty = parseFloat(row.value.quantity || 0);
  const dis = parseFloat(row.value.discount || 0);
  const tax = parseFloat(row.value.tax_rate || 0);

  const subtotal = up * qty;
  const discount_amount = subtotal * (dis / 100);
  const tax_amount = (subtotal - discount_amount) * (tax / 100);

  row.value.subtotal = subtotal;
  row.value.discount_amount = discount_amount;
  row.value.tax_amount = tax_amount;
  row.value.total = subtotal - discount_amount + tax_amount;
};

watch(
  () => [
    row.value.unit_price,
    row.value.quantity,
    row.value.discount,
    row.value.tax_rate,
  ],
  recalcRow,
);

// ── Product select ────────────────────────────────────────────────────────────

const onProductSelect = (product) => {
  row.value.product_id = product.id;
  row.value.product__label = product.name;
  row.value.name = product.name ?? row.value.name;
  row.value.unit_price = product.price ?? row.value.unit_price;
  row.value.unit = product.unit ?? row.value.unit;
  row.value.tax_rate = product.tax_rate ?? row.value.tax_rate;
  drawerOpen.value = false;
};

// ── Open / close sheet ────────────────────────────────────────────────────────

const sheetOpen = ref(false);

const openNewRow = () => {
  editingItem.value = null;
  row.value = emptyRow();
  rowErrors.value = {};
  sheetOpen.value = true;
};

const openEditRow = (item) => {
  if (props.mode !== "edit") return;
  editingItem.value = item;
  row.value = { ...item, product__label: item.product__label ?? "" };
  rowErrors.value = {};
  sheetOpen.value = true;
};

const closeSheet = () => {
  sheetOpen.value = false;
  editingItem.value = null;
  rowErrors.value = {};
};

// ── Save row ──────────────────────────────────────────────────────────────────

const saveRow = async () => {
  clearAllAlerts();
  if (!validateRow()) {
    const errorKeys = Object.values(rowErrors.value);
    error(
      errorKeys.length === 1
        ? t(errorKeys[0])
        : t("modules.line_items.errors.has_errors"),
    );
    return;
  }

  saving.value = true;
  try {
    const payload = {
      parent_type: props.parentType,
      parent_id: props.parentId,
      product_id: row.value.product_id || null,
      name: row.value.name,
      unit: row.value.unit,
      unit_price: row.value.unit_price,
      quantity: row.value.quantity,
      discount: row.value.discount,
      tax_rate: row.value.tax_rate,
      note: row.value.note,
      sort_order: editingItem.value
        ? editingItem.value.sort_order
        : items.value.length,
    };

    if (editingItem.value?.id) {
      const { data } = await axios.put(
        `/line-items/${editingItem.value.id}`,
        payload,
      );
      const idx = items.value.findIndex((i) => i.id === editingItem.value.id);
      if (idx !== -1) items.value[idx] = data;
    } else {
      const { data } = await axios.post(`/line-items`, payload);
      items.value.push(data);
    }
    success(t("modules.line_items.save_success"));

    closeSheet();
  } catch (e) {
    console.error("LineItemsPanel save error", e.message);
  } finally {
    saving.value = false;
  }
};

// ── Delete row ────────────────────────────────────────────────────────────────

const deleteRow = async (item) => {
  const ok = await confirm({
    title: t("modules.line_items.delete_title"),
    message: t("modules.line_items.delete_confirm"),
    confirmText: t("modules.line_items.delete_yes"),
    cancelText: t("modules.line_items.delete_no"),
    danger: true,
  });

  if (!ok) return;

  try {
    await axios.delete(`/line-items/${item.id}`);
    items.value = items.value.filter((i) => i.id !== item.id);
  } catch (e) {
    console.error("LineItemsPanel delete error", e.message);
  }
};

// ── Drag to reorder ───────────────────────────────────────────────────────────

const dragging = ref(null);
const dragOver = ref(null);
const originOffset = ref({ x: 0, y: 0 });
const ghostRenderPos = ref({ x: 0, y: 0 });
const dragPosition = ref({ x: 0, y: 0 });

let ghostAnimationFrame = null;

const transparentPixel = "data:image/gif;base64,R0lGODlhAQABAAAAACw=";
const dragImage = new Image();
dragImage.src = transparentPixel;

const ghostLabel = computed(() =>
  dragging.value !== null ? (items.value[dragging.value]?.name ?? "") : "",
);

const isItemDragging = (index) => dragging.value === index;
const isDropZoneActive = (index) => dragOver.value === index;

const startDrag = (index, event) => {
  dragging.value = index;
  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = "move";
    event.dataTransfer.setData("text/plain", String(index));
    try {
      event.dataTransfer.setDragImage(dragImage, 0, 0);
    } catch (_) {}
  }
  const el = event.target.closest("tr");
  if (el) {
    const rect = el.getBoundingClientRect();
    originOffset.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top,
    };
    ghostRenderPos.value = { x: event.clientX, y: event.clientY };
  }
  startGhostAnimation();
};

const endDrag = () => {
  dragging.value = null;
  dragOver.value = null;
  stopGhostAnimation();
};

const setDragOver = (index, event) => {
  event.preventDefault();
  dragOver.value = index;
};

const onDrop = async (zoneIndex, event) => {
  event.preventDefault();
  if (dragging.value === null) return;

  const fromIndex = dragging.value;
  endDrag();

  // Correct for the removal: inserting after the source shifts indices down by 1
  const toIndex = zoneIndex > fromIndex ? zoneIndex - 1 : zoneIndex;
  if (fromIndex === toIndex) return;

  const reordered = [...items.value];
  const [moved] = reordered.splice(fromIndex, 1);
  reordered.splice(toIndex, 0, moved);
  reordered.forEach((item, i) => {
    item.sort_order = i;
  });
  items.value = reordered;

  try {
    await axios.post(`/line-items/reorder`, {
      parent_type: props.parentType,
      parent_id: props.parentId,
      order: reordered.map((i) => i.id),
    });
  } catch (e) {
    console.error("LineItemsPanel reorder error", e.message);
  }
};

const onGlobalDragOver = (event) => {
  if (dragging.value === null) return;
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

const page = usePage();
const allModules = computed(() => page.props.modules);
const allLayouts = computed(() => page.props.layouts);
const appSettings = page.props.appSettings;

const getModule = (slug) => allModules.value.find((m) => m.slug === slug);
const getIcon = (slug) => getModule(slug)?.icon || "fa-solid fa-user";
const getColor = (slug) =>
  appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;

const productLinkingLayout = computed(() => {
  const layout = allLayouts.value.find((l) => l.module === "products");
  return layout?.layouts?.linkingPanel?.columns || null;
});
</script>

<template>
  <!-- ── Panel wrapper — mirrors .record-layout__sections__item ──────────── -->
  <div
    class="line-items-panel"
    :style="{ '--module-color': moduleColor }"
    @dragover="onGlobalDragOver"
  >
    <div class="line-items-panel__header">
      <button class="line-items-panel__header__add" @click="openNewRow">
        <i class="fa-solid fa-plus"></i>
      </button>
    </div>

    <!-- ── Loading skeleton ──────────────────────────────────────────────── -->
    <template v-if="loading">
      <div class="line-items-panel__table-wrap">
        <table class="line-items-panel__table">
          <thead>
            <tr>
              <th class="col-drag"></th>
              <th class="col-pos">#</th>
              <th class="col-name">
                {{ $t("modules.line_items.fields.name") }}
              </th>
              <th class="col-qty">
                {{ $t("modules.line_items.fields.quantity") }}
              </th>
              <th class="col-unit">
                {{ $t("modules.line_items.fields.unit") }}
              </th>
              <th class="col-price">
                {{ $t("modules.line_items.fields.unit_price") }}
              </th>
              <th class="col-disc">
                {{ $t("modules.line_items.fields.discount") }}
              </th>
              <th class="col-tax">
                {{ $t("modules.line_items.fields.tax_rate") }}
              </th>
              <th class="col-total">
                {{ $t("modules.line_items.fields.total") }}
              </th>
              <th class="col-actions"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in 4" :key="i" class="li-sk-row">
              <td class="col-drag"><div class="sk sk--icon"></div></td>
              <td class="col-pos"><div class="sk sk--xs"></div></td>
              <td class="col-name"><div class="sk sk--name"></div></td>
              <td class="col-qty"><div class="sk sk--sm"></div></td>
              <td class="col-unit"><div class="sk sk--sm"></div></td>
              <td class="col-price"><div class="sk sk--md"></div></td>
              <td class="col-disc"><div class="sk sk--sm"></div></td>
              <td class="col-tax"><div class="sk sk--sm"></div></td>
              <td class="col-total"><div class="sk sk--md sk--right"></div></td>
              <td class="col-actions"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="line-items-panel__totals">
        <div class="li-sk-totals">
          <div class="sk sk--tlabel"></div>
          <div class="sk sk--tval"></div>
          <div class="sk sk--tlabel"></div>
          <div class="sk sk--tval"></div>
          <div class="sk sk--tlabel sk--tbig"></div>
          <div class="sk sk--tval sk--tbig"></div>
        </div>
      </div>
    </template>

    <!-- ── Empty state ───────────────────────────────────────────────────── -->
    <div v-else-if="!items.length" class="line-items-panel__empty">
      <i class="fa-solid fa-list-ul"></i>
      <span>{{ $t("modules.line_items.no_items") }}</span>
    </div>

    <!-- ── Table ─────────────────────────────────────────────────────────── -->
    <template v-else>
      <div class="line-items-panel__table-wrap">
        <table class="line-items-panel__table">
          <thead>
            <tr>
              <th class="col-drag"></th>
              <th class="col-pos">#</th>
              <th class="col-name">
                {{ $t("modules.line_items.fields.name") }}
              </th>
              <th class="col-qty">
                {{ $t("modules.line_items.fields.quantity") }}
              </th>
              <th class="col-unit">
                {{ $t("modules.line_items.fields.unit") }}
              </th>
              <th class="col-price">
                {{ $t("modules.line_items.fields.unit_price") }}
              </th>
              <th class="col-disc">
                {{ $t("modules.line_items.fields.discount") }}
              </th>
              <th class="col-tax">
                {{ $t("modules.line_items.fields.tax_rate") }}
              </th>
              <th class="col-total">
                {{ $t("modules.line_items.fields.total") }}
              </th>
              <th class="col-actions"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              class="li-drop-zone"
              :class="{ 'li-drop-zone--active': isDropZoneActive(0) }"
              @dragover="setDragOver(0, $event)"
              @drop="onDrop(0, $event)"
            >
              <td colspan="10" class="li-drop-zone__cell"></td>
            </tr>

            <template v-for="(item, index) in items" :key="item.id">
              <tr
                class="li-row"
                :class="{ 'li-row--dragging': isItemDragging(index) }"
                draggable="true"
                @dragstart="startDrag(index, $event)"
                @dragend="endDrag"
                @click="openEditRow(item)"
              >
                <td class="col-drag">
                  <i class="fa-solid fa-grip-vertical drag-handle"></i>
                </td>
                <td class="col-pos">{{ index + 1 }}</td>
                <td class="col-name">
                  <FieldRenderer
                    :field="getLineItemField('name')"
                    v-model="item.name"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                  <span v-if="item.note" class="item-note">{{
                    item.note
                  }}</span>
                </td>
                <td class="col-qty">
                  <FieldRenderer
                    :field="getLineItemField('quantity')"
                    v-model="item.quantity"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-unit">
                  <FieldRenderer
                    :field="getLineItemField('unit')"
                    v-model="item.unit"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-price">
                  <FieldRenderer
                    :field="getLineItemField('unit_price')"
                    v-model="item.unit_price"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-disc">
                  <FieldRenderer
                    :field="getLineItemField('discount')"
                    v-model="item.discount"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-tax">
                  <FieldRenderer
                    :field="getLineItemField('tax_rate')"
                    v-model="item.tax_rate"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-total">
                  <FieldRenderer
                    :field="getLineItemField('total')"
                    v-model="item.total"
                    mode="table"
                    :module-color="moduleColor"
                    :read-only="true"
                  />
                </td>
                <td class="col-actions" @click.stop>
                  <button class="btn-delete" @click="deleteRow(item)">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </td>
              </tr>

              <tr
                class="li-drop-zone"
                :class="{ 'li-drop-zone--active': isDropZoneActive(index + 1) }"
                @dragover="setDragOver(index + 1, $event)"
                @drop="onDrop(index + 1, $event)"
              >
                <td colspan="10" class="li-drop-zone__cell"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- ── Totals row ───────────────────────────────────────────────────── -->
      <div class="line-items-panel__totals">
        <div class="totals-grid">
          <span class="totals-grid__label">{{
            $t("modules.line_items.fields.subtotal")
          }}</span>
          <span class="totals-grid__value"
            >{{ fmt(totals.subtotal) }} {{ currency }}</span
          >

          <template v-if="totals.discount_amount > 0">
            <span class="totals-grid__label">{{
              $t("modules.line_items.fields.discount_amount")
            }}</span>
            <span class="totals-grid__value discount"
              >−{{ fmt(totals.discount_amount) }} {{ currency }}</span
            >
          </template>

          <span class="totals-grid__label">{{
            $t("modules.line_items.fields.tax_amount")
          }}</span>
          <span class="totals-grid__value"
            >{{ fmt(totals.tax_amount) }} {{ currency }}</span
          >

          <span class="totals-grid__label totals-grid__label--total">{{
            $t("modules.line_items.fields.total")
          }}</span>
          <span class="totals-grid__value totals-grid__value--total"
            >{{ fmt(totals.total) }} {{ currency }}</span
          >
        </div>
      </div>
    </template>

    <!-- ════════════════════════════════════════════════════════════════════
         Row edit sheet — slides in from the right, same pattern as drawers
    ═════════════════════════════════════════════════════════════════════ -->
    <Transition name="slide-right" appear>
      <div
        v-if="sheetOpen"
        class="record-overlay"
        :style="{ '--related-color': moduleColor }"
        role="dialog"
        aria-modal="true"
        @click.self="closeSheet"
      >
        <div class="related-links">
          <!-- Header -->
          <div class="related-links__header">
            <div class="related-links__header__title">
              {{
                editingItem
                  ? $t("modules.line_items.edit_item")
                  : $t("modules.line_items.add_item")
              }}
            </div>
            <div class="related-links__header__actions">
              <button
                class="sheet-btn sheet-btn--secondary"
                @click="closeSheet"
              >
                {{ $t("modules.actions.cancel") }}
              </button>
              <button
                class="sheet-btn sheet-btn--primary"
                :disabled="!row.name || saving"
                @click="saveRow"
              >
                <i v-if="saving" class="fa-solid fa-circle-notch fa-spin"></i>
                {{ $t("modules.actions.save") }}
              </button>
            </div>
          </div>

          <!-- Body -->
          <div class="line-items-sheet__body">
            <!-- Product picker -->
            <div class="sheet-field">
              <label class="sheet-field__label">
                {{ $t("modules.line_items.fields.product_id") }}
              </label>
              <div
                class="sheet-field__record-picker"
                @click="drawerOpen = true"
              >
                <span :class="{ placeholder: !row.product__label }">
                  {{
                    row.product__label ||
                    $t("modules.line_items.select_product")
                  }}
                </span>
                <i class="fa-solid fa-chevron-right"></i>
              </div>
            </div>

            <!-- Name -->
            <div class="sheet-field">
              <label
                class="sheet-field__label"
                :class="{ 'sheet-field__label--error': rowErrors.name }"
              >
                {{ $t("modules.line_items.fields.name") }}
              </label>
              <FieldRenderer
                :field="getLineItemField('name')"
                v-model="row.name"
                mode="edit"
                :module-color="moduleColor"
                :has-error="!!rowErrors.name"
              />
            </div>

            <!-- Qty + Unit -->
            <div class="sheet-field-row">
              <div class="sheet-field">
                <label
                  class="sheet-field__label"
                  :class="{ 'sheet-field__label--error': rowErrors.quantity }"
                >
                  {{ $t("modules.line_items.fields.quantity") }}
                </label>
                <FieldRenderer
                  :field="getLineItemField('quantity')"
                  v-model="row.quantity"
                  mode="edit"
                  :module-color="moduleColor"
                  :has-error="!!rowErrors.quantity"
                />
              </div>
              <div class="sheet-field">
                <label
                  class="sheet-field__label"
                  :class="{ 'sheet-field__label--error': rowErrors.unit }"
                >
                  {{ $t("modules.line_items.fields.unit") }}
                </label>
                <FieldRenderer
                  :field="getLineItemField('unit')"
                  v-model="row.unit"
                  mode="edit"
                  :module-color="moduleColor"
                  :has-error="!!rowErrors.unit"
                />
              </div>
            </div>

            <!-- Unit price -->
            <div class="sheet-field">
              <label
                class="sheet-field__label"
                :class="{ 'sheet-field__label--error': rowErrors.unit_price }"
              >
                {{ $t("modules.line_items.fields.unit_price") }}
              </label>
              <FieldRenderer
                :field="getLineItemField('unit_price')"
                v-model="row.unit_price"
                mode="edit"
                :module-color="moduleColor"
                :has-error="!!rowErrors.unit_price"
              />
            </div>

            <!-- Discount + Tax rate -->
            <div class="sheet-field-row">
              <div class="sheet-field">
                <label
                  class="sheet-field__label"
                  :class="{ 'sheet-field__label--error': rowErrors.discount }"
                >
                  {{ $t("modules.line_items.fields.discount") }}
                </label>
                <FieldRenderer
                  :field="getLineItemField('discount')"
                  v-model="row.discount"
                  mode="edit"
                  :module-color="moduleColor"
                  :has-error="!!rowErrors.discount"
                />
              </div>
              <div class="sheet-field">
                <label
                  class="sheet-field__label"
                  :class="{ 'sheet-field__label--error': rowErrors.tax_rate }"
                >
                  {{ $t("modules.line_items.fields.tax_rate") }}
                </label>
                <FieldRenderer
                  :field="getLineItemField('tax_rate')"
                  v-model="row.tax_rate"
                  mode="edit"
                  :module-color="moduleColor"
                  :has-error="!!rowErrors.tax_rate"
                />
              </div>
            </div>

            <!-- Note -->
            <div class="sheet-field">
              <label
                class="sheet-field__label"
                :class="{ 'sheet-field__label--error': rowErrors.note }"
              >
                {{ $t("modules.line_items.fields.note") }}
              </label>
              <FieldRenderer
                :field="getLineItemField('note')"
                v-model="row.note"
                mode="edit"
                :module-color="moduleColor"
                :has-error="!!rowErrors.note"
              />
            </div>

            <!-- Live totals preview -->
            <div class="sheet-totals-preview">
              <div class="sheet-totals-preview__row">
                <span>{{ $t("modules.line_items.fields.subtotal") }}</span>
                <span>{{ fmt(row.subtotal) }}</span>
              </div>
              <div
                class="sheet-totals-preview__row"
                v-if="row.discount_amount > 0"
              >
                <span>{{
                  $t("modules.line_items.fields.discount_amount")
                }}</span>
                <span class="discount">−{{ fmt(row.discount_amount) }}</span>
              </div>
              <div class="sheet-totals-preview__row">
                <span>{{ $t("modules.line_items.fields.tax_amount") }}</span>
                <span>{{ fmt(row.tax_amount) }}</span>
              </div>
              <div
                class="sheet-totals-preview__row sheet-totals-preview__row--total"
              >
                <span>{{ $t("modules.line_items.fields.total") }}</span>
                <span>{{ fmt(row.total) }} {{ currency }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Smooth drag ghost — follows cursor with lerp, hides browser default -->
    <div
      v-if="dragging !== null"
      class="li-ghost"
      :style="{
        top: ghostRenderPos.y - originOffset.y + 'px',
        left: ghostRenderPos.x - originOffset.x + 'px',
      }"
    >
      <i class="fa-solid fa-grip-vertical li-ghost__handle"></i>
      <span class="li-ghost__label">{{ ghostLabel }}</span>
    </div>

    <!-- Product selector drawer (nested, reuses your existing component) -->
    <RecordSelectorDrawer
      :open="drawerOpen"
      search-endpoint="/relatedfield/search/products"
      related-module="products"
      :icon="getIcon('products')"
      :accent-color="getColor('products')"
      :layout="productLinkingLayout"
      :fields="productFields"
      @select="onProductSelect"
      @close="drawerOpen = false"
    />
  </div>
</template>

<style lang="scss" scoped>
.line-items-panel {
  border-radius: 8px;
  overflow: hidden;
  user-select: none;

  &__header {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 16px 20px 12px;

    &__add {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      height: 36px;
      padding: 0 14px;
      border-radius: 8px;
      font-weight: 500;
      font-family: inherit;
      white-space: nowrap;
      color: white;
      background: var(--module-color);
      border: none;
      outline: none;
      cursor: pointer;
      transition:
        background 0.15s ease,
        opacity 0.15s ease;

      &:hover {
        background: color-mix(in srgb, var(--module-color) 80%, white 20%);
      }
    }
  }

  &__loading,
  &__empty {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 32px 20px;
    color: #9ca3af;
  }

  &__table-wrap {
    overflow-x: auto;
    border-top: 1px solid #f3f4f6;
  }

  &__table {
    width: 100%;
    border-collapse: collapse;

    thead tr {
      border-bottom: 1px solid #e9eaec;
    }

    th {
      padding: 8px 12px;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #9ca3af;
      text-align: left;
      white-space: nowrap;
      font-size: 0.8rem;
      &:nth-last-child(2) {
        text-align: right;
      }
    }

    tbody tr {
      transition: background 0.1s;
    }

    td {
      padding: 10px 12px;
      color: #374151;
      vertical-align: middle;
      font-size: 0.95rem;
      text-wrap: nowrap;
    }
  }

  &__totals {
    display: flex;
    justify-content: flex-end;
    padding: 16px 20px;
    border-top: 1px solid #e9eaec;
  }
}

.col-drag {
  width: 24px;
  padding: 0 4px 0 12px;
}

.col-pos {
  width: 28px;
  color: #9ca3af;
}

.col-name {
  min-width: 160px;
}

.col-qty,
.col-unit {
  width: 70px;
}

.col-price,
.col-disc,
.col-tax {
  width: 90px;
}

.col-total {
  width: 100px;
  font-weight: 500;
  text-align: right;
}

.col-actions {
  width: 36px;
  text-align: right;
}

.item-name {
  display: block;
  font-weight: 500;
}

.item-note {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  margin-top: 2px;
}

.muted {
  color: #d1d5db;
}

.drag-handle {
  color: #d1d5db;
  cursor: grab;
  font-size: 12px;

  &:active {
    cursor: grabbing;
  }
}

.btn-delete {
  background: none;
  border: none;
  color: #d1d5db;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: color 0.15s;
  font-size: 12px;

  &:hover {
    color: var(--danger-color);
  }
}

.li-row {
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;

  &:hover {
    background: #f9fafb;
  }

  &--dragging {
    opacity: 0.4;
  }
}

.li-drop-zone {
  &__cell {
    padding: 0 !important;
    height: 2px;
    background: transparent;
    border-radius: 2px;
    transition: all 0.2s;
  }

  &--active &__cell {
    height: 36px;
    background: color-mix(in srgb, var(--module-color) 8%, transparent);
    border: 1.5px dashed
      color-mix(in srgb, var(--module-color) 45%, transparent) !important;
    border-radius: 4px;
  }
}

.li-ghost {
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
    color: #212529;
    white-space: nowrap;
  }
}

.totals-grid {
  display: grid;
  grid-template-columns: auto auto;
  gap: 4px 24px;
  text-align: right;

  &__label {
    color: #6b7280;

    &--total {
      font-weight: 600;
      color: #111;
      padding-top: 8px;
      border-top: 2px solid #111;
    }
  }

  &__value {
    color: #111;
    font-variant-numeric: tabular-nums;

    &.discount {
      color: #ef4444;
    }

    &--total {
      font-weight: 700;
      padding-top: 8px;
      border-top: 2px solid #111;
    }
  }
}

.line-items-sheet__body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  overflow-y: auto;
  flex: 1;
}

.sheet-field {
  display: flex;
  flex-direction: column;
  gap: 5px;

  &-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  &__label {
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #9ca3af;

    &--error {
      color: #ef4444;
    }
  }

  &__record-picker {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 2.5rem;
    padding: 0 0.75rem;
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);

    &:hover {
      border-color: var(--module-color);
    }

    .placeholder {
      color: #9ca3af;
      padding: 0.625rem 0;
    }

    i {
      color: #9ca3af;
      font-size: 11px;
    }
  }
}

.sheet-totals-preview {
  border-top: 1px solid #f3f4f6;
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 5px;

  &__row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6b7280;

    span:last-child {
      font-variant-numeric: tabular-nums;
      color: #111;
    }

    .discount {
      color: #ef4444;
    }

    &--total {
      font-weight: 600;
      font-size: 13px;
      color: #111;
      padding-top: 6px;
      border-top: 1px solid #e5e7eb;
      margin-top: 4px;

      span:last-child {
        color: var(--module-color);
      }
    }
  }
}

.sheet-btn {
  border-radius: 6px;
  padding: 8px 16px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  background-color: var(--related-color);
  color: white;

  &--secondary {
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;

    &:hover {
      background: #f9fafb;
    }
  }

  &--primary {
    border: none;
    background: var(--module-color);
    color: #fff;

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    &:not(:disabled):hover {
      opacity: 0.88;
    }
  }
}

@keyframes sk-shimmer {
  0% {
    background-position: -600px 0;
  }
  100% {
    background-position: 600px 0;
  }
}

.sk {
  border-radius: 4px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e4e4e4 50%, #f0f0f0 75%);
  background-size: 1200px 100%;
  animation: sk-shimmer 1.4s infinite linear;

  &--icon {
    width: 10px;
    height: 14px;
  }
  &--xs {
    width: 18px;
    height: 12px;
  }
  &--sm {
    width: 44px;
    height: 12px;
  }
  &--md {
    width: 72px;
    height: 12px;
  }
  &--name {
    width: 150px;
    height: 14px;
  }
  &--right {
    margin-left: auto;
  }
  &--tlabel {
    width: 90px;
    height: 12px;
  }
  &--tval {
    width: 110px;
    height: 12px;
  }
  &--tbig {
    height: 16px;
    margin-top: 8px;
  }
}

.li-sk-row td {
  padding: 14px 12px;
}

.li-sk-totals {
  display: grid;
  grid-template-columns: auto auto;
  gap: 10px 24px;
  justify-items: end;
}
</style>
