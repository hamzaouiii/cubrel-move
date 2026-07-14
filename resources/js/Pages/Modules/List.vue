<script setup>
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
  watch,
  nextTick,
} from "vue";
import { Head, usePage, Link, useForm, router } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";
import ListDeleteZone from "@/Pages/Components/Modules/ListActions/ListDeleteZone.vue";
import MassUpdateZone from "@/Pages/Components/Modules/ListActions/MassUpdateZone.vue";
import ExportZone from "@/Pages/Components/Modules/ListActions/ExportZone.vue";
import FilterZone from "@/Pages/Components/Modules/ListActions/FilterZone.vue";
import Selectbox from "@/Pages//Components/FiledTypes/Selectbox.vue";
import ExportModal from "@/Pages/Components/Modules/ExportModal.vue";
import ImportModal from "@/Pages/Components/Modules/ImportModal.vue";

const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();

defineOptions({
  layout: AppLayout,
});

const props = defineProps({
  module: Object,
  items: Array,
  meta: Object,
  listLayout: Object,
  filters: Object,
  fields: Object,
  filterableFields: { type: Array, default: () => [] },
  availableFilters: { type: Array, default: () => [] },
  activeFilter: { type: Object, default: null },
  importMaxFileSizeKb: { type: Number, default: null },
  importAcceptedExtensions: { type: Array, default: () => [] },
  importExcludedFieldTypes: { type: Array, default: () => [] },
});

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const appSettings = page.props.appSettings;

const bulkActionmode = ref(false);
const showDeleteZone = ref(false);
const showMassUpdateZone = ref(false);
const showExportZone = ref(false);
const showExportModal = ref(false);
const showImportModal = ref(false);

// allMatchingSelected = true means "every record in the result set is selected,
// EXCEPT those in excludedIds".
const allMatchingSelected = ref(false);
const allInViewSelected = ref(false);
const selectedIds = ref([]); // used when NOT in allMatching mode
const excludedIds = ref([]); // used ONLY in allMatching mode

const showActionDropDown = ref(false);
const actionDropDownref = ref(null);

const searchInput = ref(null);
const search = ref(props.filters.search ?? "");
const sortKey = ref(props.filters.sort ?? null);
const sortDir = ref(props.filters.direction ?? "asc");
const showListSearch = ref(false);

const updateForm = useForm({
  allMatchingSelected: null,
  selectedIds: {},
  excludedIds: {},
  filters: {},
  field: "",
  value: "",
});

const listLayoutColumns = computed(() => {
  return Object.values(props.listLayout?.columns || {}).filter(
    (column) => column !== null && getField(column),
  );
});

// ─── Derived selection helpers ───────────────────────────────────────────────

/**
 * True when every item on the current page is effectively selected.
 * Derived purely from item arrays — never from the allInViewSelected flag.
 */
const allSelected = computed(() => {
  if (!props.items?.length) return false;
  if (allMatchingSelected.value) {
    return props.items.every((i) => !excludedIds.value.includes(i.id));
  }
  return props.items.every((i) => selectedIds.value.includes(i.id));
});

/**
 * True when SOME but not all page items are selected — drives the dash icon.
 * Derived purely from item arrays — never from the allInViewSelected flag.
 */
const someInViewSelected = computed(() => {
  if (!props.items?.length) return false;
  if (allMatchingSelected.value) {
    // at least one page item is in the excluded list
    return excludedIds.value.some((id) => props.items.some((i) => i.id === id));
  }
  const someChecked = props.items.some((i) => selectedIds.value.includes(i.id));
  const allChecked = props.items.every((i) => selectedIds.value.includes(i.id));
  return someChecked && !allChecked;
});

/**
 * How many records are effectively selected.
 */
const totalSelected = computed(() => {
  if (allMatchingSelected.value) {
    return (props.meta?.total ?? 0) - excludedIds.value.length;
  }
  return selectedIds.value.length;
});

/**
 * Whether to show the "Select all N records in result set" prompt.
 * Only shown when the user has selected all records on the page but has NOT
 * yet escalated to allMatching mode.
 */
const showSelectAllPrompt = computed(() => {
  return (
    !allMatchingSelected.value &&
    allInViewSelected.value &&
    props.meta &&
    props.meta.total > (props.items?.length ?? 0)
  );
});

const recordsNumber = computed(() => props.items?.length ?? 0);

const recordsNumberPhrase = computed(() => {
  if (!props.meta) return "(0)";
  return `${recordsNumber.value} ${t("modules.of")} ${props.meta.total}`;
});

const editModuleUrl = computed(() => {
  const base = "settings/modules/";
  return base + props.module.id;
});

const module_color = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color;
});

const getField = (item) => {
  return Object.values(props.fields)?.find((field) => field.name === item.name);
};

const isSelected = (id) => {
  if (allMatchingSelected.value) {
    return !excludedIds.value.includes(id);
  }
  return selectedIds.value.includes(id);
};

const toggleRow = (id) => {
  if (allMatchingSelected.value) {
    // In allMatching mode: toggling a selected row adds it to excludedIds.
    if (excludedIds.value.includes(id)) {
      // Re-selecting an excluded row — remove from excluded.
      excludedIds.value = excludedIds.value.filter((x) => x !== id);
    } else {
      // Deselecting a row — add to excluded.
      excludedIds.value = [...excludedIds.value, id];
    }
    return;
  }

  // Normal mode
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id);
  } else {
    selectedIds.value = [...selectedIds.value, id];
  }

  // Keep allInViewSelected in sync
  allInViewSelected.value =
    props.items?.every((i) => selectedIds.value.includes(i.id)) ?? false;
};

// ─── Page-level "select all in view" (header checkbox) ───────────────────────

const toggleAllInView = () => {
  if (!props.items?.length) return;

  if (allMatchingSelected.value) {
    // In allMatching mode: toggle means either clear all exclusions on this
    // page (re-select) or exclude all page items.
    const pageIds = props.items.map((i) => i.id);
    const allPageSelected = pageIds.every(
      (id) => !excludedIds.value.includes(id),
    );

    if (allPageSelected) {
      // Exclude all page items
      excludedIds.value = Array.from(
        new Set([...excludedIds.value, ...pageIds]),
      );
    } else {
      // Remove page items from excluded (re-select them)
      excludedIds.value = excludedIds.value.filter(
        (id) => !pageIds.includes(id),
      );
    }
    return;
  }

  // Normal mode
  allInViewSelected.value = !allInViewSelected.value;
  const allIn = props.items.every((i) => selectedIds.value.includes(i.id));

  selectedIds.value = allIn
    ? selectedIds.value.filter((id) => !props.items.some((i) => i.id === id))
    : Array.from(
        new Set([...selectedIds.value, ...props.items.map((i) => i.id)]),
      );
};

// ─── "Select all N records in result set" ────────────────────────────────────

const selectAllMatching = () => {
  allMatchingSelected.value = true;
  allInViewSelected.value = false;
  excludedIds.value = [];
  // selectedIds is no longer the source of truth in this mode but keep it
  // populated with page items for any component that reads it as a hint.
  selectedIds.value = props.items?.map((i) => i.id) ?? [];
};

// ─── Clear / cancel ──────────────────────────────────────────────────────────

const clearSelection = () => {
  allMatchingSelected.value = false;
  allInViewSelected.value = false;
  selectedIds.value = [];
  excludedIds.value = [];
};

// ─── Bulk action zone toggles ─────────────────────────────────────────────────

const toggleMassUpdateZone = () => {
  showMassUpdateZone.value = !showMassUpdateZone.value;

  if (
    showMassUpdateZone.value === true ||
    showDeleteZone.value === true ||
    showExportZone.value === true
  ) {
    bulkActionmode.value = true;
  } else {
    bulkActionmode.value = false;
  }
  clearSelection();
  showActionDropDown.value = false;
  if (showDeleteZone.value == true) {
    showDeleteZone.value = false;
  }
  if (showExportZone.value == true) {
    showExportZone.value = false;
  }
};

const toggleDeleteZone = () => {
  showDeleteZone.value = !showDeleteZone.value;

  if (
    showMassUpdateZone.value === true ||
    showDeleteZone.value === true ||
    showExportZone.value === true
  ) {
    bulkActionmode.value = true;
  } else {
    bulkActionmode.value = false;
  }
  clearSelection();
  showActionDropDown.value = false;
  if (showMassUpdateZone.value == true) {
    showMassUpdateZone.value = false;
  }
  if (showExportZone.value == true) {
    showExportZone.value = false;
  }
};

const toggleExportZone = () => {
  showExportZone.value = !showExportZone.value;

  if (
    showMassUpdateZone.value === true ||
    showDeleteZone.value === true ||
    showExportZone.value === true
  ) {
    bulkActionmode.value = true;
  } else {
    bulkActionmode.value = false;
  }
  clearSelection();
  showActionDropDown.value = false;
  if (showDeleteZone.value == true) {
    showDeleteZone.value = false;
  }
  if (showMassUpdateZone.value == true) {
    showMassUpdateZone.value = false;
  }
};

// ─── Action bar ──────────────────────────────────────────────────────────────

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value;
};

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

// ─── Search ──────────────────────────────────────────────────────────────────

const performSearch = (page = 1) => {
  router.get(
    window.location.pathname,
    {
      search: search.value || undefined,
      sort: sortKey.value || undefined,
      direction: sortDir.value,
      filter: props.filters?.filter || undefined,
      page,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
};

const handleSearchInput = () => {
  if (search.value.length >= 3 || search.value.length === 0) {
    clearSelection();
    performSearch(1);
  }
};

const resetSearchValue = () => {
  search.value = "";
  handleSearchInput();
};

// ─── Navigation ──────────────────────────────────────────────────────────────

const goToCreateView = () => {
  const moduleName = page.props.module.slug;
  router.visit(`/${moduleName}/create`);
};

const goToLink = (path) => {
  router.visit(path);
};

// ─── Import ──────────────────────────────────────────────────────────────────

const handleImportModalClose = ({ imported } = {}) => {
  showImportModal.value = false;
  if (imported) {
    // Import writes go through axios, not Inertia's router, so items/meta
    // were never refreshed automatically — pull the current list state.
    router.reload({ only: ["items", "meta"] });
  }
};

const resetActionZone = () => {
  showDeleteZone.value = false;
  showMassUpdateZone.value = false;
  showExportZone.value = false;
  bulkActionmode.value = false;
  showActionDropDown.value = false;
  clearSelection();
};

// ─── Delete ──────────────────────────────────────────────────────────────────

const handleListDelete = async () => {
  const count = totalSelected.value;

  const ok = await confirm({
    title: t("modules.delete.confirm_delete"),
    message: t("modules.delete.confirm_delete_message", { count: count }),
    confirmText: t("modules.actions.delete_yes"),
    cancelText: t("modules.actions.delete_no"),
    danger: true,
  });

  if (!ok) return;

  info(t("modules.actions.deleting"));

  router.delete(`/${props.module.slug}`, {
    data: {
      allMatchingSelected: allMatchingSelected.value,
      selectedIds: selectedIds.value,
      excludedIds: excludedIds.value,
      filters: props.filters ?? {},
    },
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("modules.actions.delete_success"));
      clearSelection();
      resetActionZone();
      // in case user is not on default page and there are not enough records to justify current page. Always redirect to default module list view. ( not needed in mass update)
      router.visit(`/${props.module.slug}`);
    },
    onError: () => {
      clearAllAlerts();
      error(t("modules.actions.delete_error"));
    },
  });
};

// ─── Mass update ─────────────────────────────────────────────────────────────

const handleMassUpdate = async (payload) => {
  const count = payload.allMatchingSelected
    ? props.meta.total - (payload.excludedIds?.length ?? 0)
    : payload.selectedIds.length;

  const ok = await confirm({
    title: t("modules.update.confirm_update") ?? "Confirm Update",
    message:
      t("modules.update.confirm_update_message", {
        count,
        field: payload.field_key,
      }) ?? `You are about to update ${count} records. Continue?`,
    confirmText: t("modules.update.update_yes") ?? "Update",
    cancelText: t("modules.update.update_no") ?? "Cancel",
    danger: true,
  });

  if (!ok) return;

  info(t("modules.actions.updating"));

  updateForm.allMatchingSelected = payload.allMatchingSelected;
  updateForm.selectedIds = payload.selectedIds;
  updateForm.excludedIds = payload.excludedIds ?? [];
  updateForm.filters = payload.filters ?? {};
  updateForm.field = payload.field;
  updateForm.value = payload.value;

  updateForm.put(`/${props.module.slug}`, {
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("modules.actions.update_success"));
      clearSelection();
      resetActionZone();
    },
    onError: () => {
      clearAllAlerts();
      error(t("modules.actions.update_error"));
    },
  });
};

// ─── Sort ────────────────────────────────────────────────────────────────────

const isSortable = (col) => col?.sortable === true;
const isSorted = (col) => sortKey.value === col.name;

const sortIcon = (col) => {
  if (!isSortable(col)) return "";
  if (!isSorted(col)) return "fa-solid fa-sort";
  return sortDir.value === "asc"
    ? "fa-solid fa-sort-up"
    : "fa-solid fa-sort-down";
};

const sortBy = (col) => {
  if (!isSortable(col)) return;

  if (sortKey.value === col.name) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = col.name;
    sortDir.value = "asc";
  }

  router.get(
    window.location.pathname,
    {
      search: search.value || undefined,
      sort: sortKey.value,
      direction: sortDir.value,
      filter: props.filters?.filter || undefined,
      page: 1,
    },
    { preserveState: true, preserveScroll: true, replace: true },
  );
};

// ─── Saved filters ───────────────────────────────────────────────────────────

const applyFilter = (filterKey) => {
  router.get(
    window.location.pathname,
    {
      search: search.value || undefined,
      sort: sortKey.value || undefined,
      direction: sortDir.value,
      filter: filterKey || undefined,
      page: 1,
    },
    { preserveState: true, preserveScroll: true, replace: true },
  );
};

const clearFilter = () => {
  router.get(
    window.location.pathname,
    {
      search: search.value || undefined,
      sort: sortKey.value || undefined,
      direction: sortDir.value,
      page: 1,
    },
    { preserveState: true, preserveScroll: true, replace: true },
  );
};

const toggleSearch = () => {
  showListSearch.value = !showListSearch.value;
  if (showListSearch.value) {
    nextTick(() => {
      searchInput.value?.focus();
    });
  } else {
    resetSearchValue();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
});

// ─── Row click ───────────────────────────────────────────────────────────────

const handleRowClick = (id) => {
  if (!bulkActionmode.value) {
    router.visit(`/${props.module.slug}/${id}`);
  } else {
    toggleRow(id);
  }
};

const hidePagination = computed(() => {
  return props.meta?.total < props.meta?.perPage;
});

const isAdmin = computed(() => {
  return page.props?.auth?.user?.is_admin || false;
});
</script>

<template>
  <Head>
    <title>{{ $t(module.label) }} - Cubrel</title>
  </Head>

  <div class="list-layout" :style="{ '--module-color': module_color }">
    <div class="list-layout__header">
      <div class="list-layout__header__details">
        <h3 class="list-layout__header__details__title">
          {{ $t(module.label) }}
        </h3>
        <span class="list-layout__header__details__meta">{{
          recordsNumberPhrase
        }}</span>
      </div>

      <div class="list-layout__header__actions" ref="actionDropDownref">
        <div
          class="list-layout__header__actions__list"
          :style="
            appSettings.use_individual_module_colors == '0'
              ? { '--module-color': appSettings.primary_color }
              : { '--module-color': module.color }
          "
        >
          <Transition name="slide-search">
            <div
              class="list-layout__header__actions__list__search"
              v-if="showListSearch"
            >
              <input
                ref="searchInput"
                type="text"
                name="search"
                :placeholder="$t('modules.actions.search_placeholder')"
                v-model="search"
                @input="handleSearchInput"
                @keydown.enter.prevent="performSearch(1)"
              />
            </div>
          </Transition>

          <button @click="toggleSearch()">
            <i
              class="fa-solid"
              :class="showListSearch ? 'fa-xmark' : ' fa-magnifying-glass'"
            ></i>
          </button>
          <button @click="goToCreateView()">
            {{ $t("modules.actions.create") }}
          </button>

          <button @click="toggleActionDropDown">
            <i
              :class="
                showActionDropDown
                  ? 'fa-solid fa-chevron-up'
                  : 'fa-solid fa-chevron-down'
              "
            ></i>
          </button>

          <transition name="fade">
            <ul
              v-if="showActionDropDown"
              class="list-layout__header__actions__list__dropdown show"
            >
              <li v-if="isAdmin">
                <span
                  class="list-layout__header__actions__list__dropdown__item"
                  @click="goToLink(editModuleUrl)"
                >
                  <i class="fa-solid fa-wrench"></i>
                  {{ $t("modules.actions.edit_module") }}
                  <i
                    class="list-layout__header__actions__list__dropdown__item--link-icon fa-solid fa-arrow-up-right-from-square"
                  ></i>
                </span>
              </li>
              <li>
                <span
                  class="list-layout__header__actions__list__dropdown__item"
                  @click="toggleMassUpdateZone()"
                >
                  <i class="fa-solid fa-square-pen"></i>
                  {{ $t("modules.actions.mass_update") }}
                </span>
              </li>
              <li>
                <span
                  class="list-layout__header__actions__list__dropdown__item"
                  @click="toggleExportZone()"
                >
                  <i class="fa-solid fa-download"></i>
                  {{ $t("modules.actions.export") }}
                </span>
              </li>
              <li>
                <span
                  class="list-layout__header__actions__list__dropdown__item"
                  @click="
                    showImportModal = true;
                    showActionDropDown = false;
                  "
                >
                  <i class="fa-solid fa-upload"></i>
                  {{ $t("modules.actions.import") }}
                </span>
              </li>
              <li>
                <span
                  class="list-layout__header__actions__list__dropdown__item list-layout__header__actions__list__dropdown__item--delete"
                  @click.prevent="toggleDeleteZone()"
                >
                  <i class="fa-solid fa-trash-can"></i>
                  {{ $t("modules.actions.mass_delete") }}
                </span>
              </li>
            </ul>
          </transition>
        </div>
      </div>
    </div>

    <ListDeleteZone
      v-if="showDeleteZone"
      :selectedIds="selectedIds"
      :excludedIds="excludedIds"
      :meta="meta"
      :allMatchingSelected="allMatchingSelected"
      @selectAllMatching="selectAllMatching()"
      @cancelClicked="resetActionZone()"
      @clearSelection="clearSelection()"
      @deleteClicked="handleListDelete()"
    />

    <MassUpdateZone
      v-else-if="showMassUpdateZone"
      :selected-ids="selectedIds"
      :excluded-ids="excludedIds"
      :meta="meta"
      :all-matching-selected="allMatchingSelected"
      :fields="fields"
      :filters="props.filters ?? {}"
      @massUpdate="handleMassUpdate"
      @selectAllMatching="selectAllMatching()"
      @clearSelection="clearSelection()"
      @cancelClicked="resetActionZone()"
    />

    <ExportZone
      v-else-if="showExportZone"
      :selectedIds="selectedIds"
      :excludedIds="excludedIds"
      :meta="meta"
      :allMatchingSelected="allMatchingSelected"
      @selectAllMatching="selectAllMatching()"
      @clearSelection="clearSelection()"
      @cancelClicked="resetActionZone()"
      @exportClicked="showExportModal = true"
    />

    <ExportModal
      v-if="showExportModal"
      mode="bulk"
      :module-slug="module.slug"
      :selection="{
        selectedIds,
        excludedIds,
        allMatchingSelected,
        filters: props.filters ?? {},
      }"
      @cancel="showExportModal = false"
      @close="
        showExportModal = false;
        resetActionZone();
      "
    />

    <ImportModal
      v-if="showImportModal"
      :module-slug="module.slug"
      :fields="fields"
      :max-file-size-kb="importMaxFileSizeKb"
      :accepted-extensions="importAcceptedExtensions"
      :excluded-field-types="importExcludedFieldTypes"
      @close="handleImportModalClose"
    />

    <FilterZone
      :filterable-fields="filterableFields"
      :available-filters="availableFilters"
      :active-filter="activeFilter"
      :module-slug="module.slug"
      @applyFilter="applyFilter"
      @clearFilter="clearFilter"
      @cancelClicked="resetActionZone()"
    />

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
            <th
              v-if="bulkActionmode"
              scope="col"
              class="list-layout__table__bulk-select"
            >
              <Selectbox
                :value="'all'"
                :modelValue="allSelected || someInViewSelected ? ['all'] : []"
                @update:modelValue="toggleAllInView"
                :color="module_color"
                :current-page-all="someInViewSelected && !allSelected"
              />
            </th>

            <th
              v-for="col in listLayoutColumns || []"
              :key="col?.name"
              scope="col"
              :class="{ sortable: getField(col)?.sortable }"
              @click="sortBy(getField(col))"
            >
              <span class="th-label">
                {{ $t(col.label) }}
                <i
                  v-if="getField(col)?.sortable"
                  :class="sortIcon(getField(col))"
                  class="sort-icon"
                ></i>
              </span>
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-if="meta && meta.total != 0">
            <tr
              v-for="item in items"
              :key="item.id"
              class="clickable-row"
              :class="{ 'selected-row': isSelected(item.id) }"
              @click="handleRowClick(item.id)"
            >
              <td
                v-if="bulkActionmode"
                @click.stop
                class="list-layout__table__bulk-select"
              >
                <Selectbox
                  :modelValue="isSelected(item.id)"
                  @update:modelValue="() => toggleRow(item.id)"
                  :color="module_color"
                />
              </td>

              <td v-for="col in listLayoutColumns || []" :key="col.name">
                <FieldRenderer
                  :field="getField(col)"
                  v-model="item[col.name]"
                  mode="table"
                  :module-color="module_color"
                  :highlight="search"
                  :searchable="getField(col)?.searchable"
                  :related_label="item[col.name + '__label'] ?? null"
                />
              </td>
            </tr>
          </template>

          <template v-else>
            <tr class="no-data-row">
              <td
                :colspan="
                  (listLayoutColumns?.length ?? 0) + (bulkActionmode ? 1 : 0)
                "
                class="no_data_list_view"
              >
                {{ $t("modules.defaults.no_data") }}
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="list-layout__pagination" v-if="!hidePagination">
      <Pagination v-if="meta && meta.total != 0" :meta="meta" />
    </div>
  </div>
</template>
