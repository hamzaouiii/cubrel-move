<script setup>
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  getCurrentInstance,
  watch,
} from "vue";
import { Head, usePage, Link, router } from "@inertiajs/vue3";
import { formatDateTime, formatDate } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";

import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";
import ListDeleteZone from "@/Pages/Components/Modules/ListActions/ListDeleteZone.vue";
import MassUpdateZone from "@/Pages/Components/Modules/ListActions/MassUpdateZone.vue";

const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();

defineOptions({
  layout: Layout,
});
const { props } = usePage();

const pageProps = defineProps({
  module: Object,
  title: String,
  items: Array,
  meta: Object,
  listLayout: Object,
  filters: Object,
  fields: Object,
});
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const bulkActionmode = ref(false);
const showDeleteZone = ref(false);
const showMassUpdateZone = ref(false);
const selectedIds = ref([]);
const showActionDropDown = ref(false);
const actionDropDownref = ref(null);
const allMatchingSelected = ref(false);

const listLayoutColumns = computed(() => {
  return Object.values(props.listLayout?.columns || {}).filter(
    (column) => column !== null,
  );
});

const isSelected = (id) => selectedIds.value.includes(id);

const toggleRow = (id) => {
  if (allMatchingSelected.value) allMatchingSelected.value = false;

  if (isSelected(id)) {
    selectedIds.value = selectedIds.value.filter((x) => x !== id);
  } else {
    selectedIds.value = [...selectedIds.value, id];
  }
};

const toggleMassUpdateZone = () => {
  showMassUpdateZone.value = !showMassUpdateZone.value;
  if (showDeleteZone.value == true) {
    showDeleteZone.value = false;
  }
  toggleBulkActionMode();
};

const toggleDeleteZone = () => {
  showDeleteZone.value = !showDeleteZone.value;
  if (showMassUpdateZone.value == true) {
    showMassUpdateZone.value = false;
  }
  toggleBulkActionMode();
};

const allSelected = computed(() => {
  if (!pageProps.items?.length) return false;

  if (allMatchingSelected.value) return true;

  return pageProps.items.every((i) => selectedIds.value.includes(i.id));
});

const toggleAll = () => {
  if (!pageProps.meta || pageProps.meta.total === 0) return;

  if (allMatchingSelected.value) {
    allMatchingSelected.value = false;
    selectedIds.value = [];
    return;
  }

  allMatchingSelected.value = true;
  selectedIds.value = pageProps.items?.map((i) => i.id) ?? [];
};

const toggleAllInView = () => {
  if (!pageProps.items?.length) return;

  allMatchingSelected.value = false;

  const allInViewSelected = pageProps.items.every((i) =>
    selectedIds.value.includes(i.id),
  );

  selectedIds.value = allInViewSelected
    ? selectedIds.value.filter(
        (id) => !pageProps.items.some((i) => i.id === id),
      )
    : Array.from(
        new Set([...selectedIds.value, ...pageProps.items.map((i) => i.id)]),
      );
};

const clearSelection = () => {
  allMatchingSelected.value = false;
  selectedIds.value = [];
};

watch(
  () => pageProps.items,
  (newItems) => {
    // keep current page ids when "all matching" is enabled (for checkbox rendering)
    if (allMatchingSelected.value) {
      selectedIds.value = (newItems ?? []).map((i) => i.id);
    }
  },
);

const recordsNumber = computed(() => pageProps.items?.length ?? 0);
const recordsNumberPhrase = computed(() => {
  if (!pageProps.meta) return "(0)";
  return `${recordsNumber.value} ${t("modules.of")} ${pageProps.meta.total}`;
});

const toggleActionDropDown = () => {
  showActionDropDown.value = !showActionDropDown.value;
};

const toggleBulkActionMode = () => {
  bulkActionmode.value = !bulkActionmode.value;
  clearSelection();
  showActionDropDown.value = false;
};

const handleClickOutsideActionDropDown = (event) => {
  if (
    actionDropDownref.value &&
    !actionDropDownref.value.contains(event.target)
  ) {
    showActionDropDown.value = false;
  }
};

const search = ref(pageProps.filters.search ?? "");

const performSearch = (page = 1) => {
  router.get(
    window.location.pathname,
    {
      search: search.value || undefined,
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

onMounted(() => {
  document.addEventListener("click", handleClickOutsideActionDropDown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutsideActionDropDown);
});

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

const highlightMatch = (text) => {
  if (!text) return "-";
  if (!search.value || !search.value.trim()) return text;

  const term = escapeRegExp(search.value.trim());
  const regex = new RegExp(`(${term})`, "gi");

  return text
    .toString()
    .replace(regex, '<span class="search-highlight">$1</span>');
};

const appSettings = usePage().props.appSettings;

const resetSearchValue = () => {
  search.value = "";
  handleSearchInput();
};

function goToCreateView() {
  const moduleName = usePage().props.module.slug;
  router.visit(`/${moduleName}/create`);
}
const editModuleUrl = computed(() => {
  const base = "settings/modules/";
  return base + props.module.id;
});

const resetActionZone = () => {
  showDeleteZone.value = false;
  showMassUpdateZone.value = false;
  bulkActionmode.value = false;
  showActionDropDown.value = false;
  clearSelection();
};

const totalSelected = computed(() => {
  return allMatchingSelected.value
    ? (pageProps.meta?.total ?? 0)
    : selectedIds.value.length;
});

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

  router.delete(`/${pageProps.module.slug}`, {
    data: {
      allMatchingSelected: allMatchingSelected.value,
      selectedIds: selectedIds.value,
      filters: pageProps.filters ?? {},
    },
    preserveScroll: true,
    onSuccess: () => {
      clearAllAlerts();
      success(t("modules.actions.delete_success"));
      clearSelection();
      resetDeleteZone();
    },
    onError: () => {
      clearAllAlerts();
      error(t("modules.actions.delete_error"));
    },
  });
};

const handleMassUpdate = async (payload) => {
  const count = payload.allMatchingSelected
    ? meta.total
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

  // info(t("modules.actions.updating"));
  error(
    "Mass update will not be sent to the backend until field types are introduced ",
  );

  // router.put(`/${pageProps.module.slug}`, {
  //   // or: router.patch(...) OR router.put(`/${pageProps.module.slug}/mass-update`, { ... })
  //   data: {
  //     allMatchingSelected: payload.allMatchingSelected,
  //     selectedIds: payload.selectedIds,
  //     filters: payload.filters ?? {},

  //     field_key: payload.field_key,
  //     new_value: payload.new_value,
  //   },
  //   preserveScroll: true,
  //   onSuccess: () => {
  //     clearAllAlerts();
  //     success(t("modules.actions.update_success"));
  //     clearSelection();
  //     // optional: reset your mass update UI
  //     // resetMassUpdateZone();
  //   },
  //   onError: () => {
  //     clearAllAlerts();
  //     error(t("modules.actions.update_error"));
  //   },
  // });
};

const getFieldDropDownList = (f) => {
  const field = pageProps.fields.find((field) => field.name === f);

  return field?.dropdown_list?.values || [];
};

const getDropDownListLabel = (f, i) => {
  const list = getFieldDropDownList(f);
  const label = list.find((l) => l.value === i)?.label || "";
  return t(label);
};

const sortKey = ref(null);
const sortDir = ref("asc");

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
};

const sortedItems = computed(() => {
  if (!sortKey.value) return pageProps.items;

  return [...pageProps.items].sort((a, b) => {
    const valA = a[sortKey.value];
    const valB = b[sortKey.value];

    if (valA == null) return 1;
    if (valB == null) return -1;

    if (typeof valA === "number" && typeof valB === "number") {
      return sortDir.value === "asc" ? valA - valB : valB - valA;
    }

    return sortDir.value === "asc"
      ? String(valA).localeCompare(String(valB))
      : String(valB).localeCompare(String(valA));
  });
});
</script>

<template>
  <Head>
    <title>{{ title }}</title>
  </Head>

  <div
    class="list-layout"
    :style="
      appSettings.use_individual_module_colors == '0'
        ? { '--module-color': appSettings.primary_color }
        : { '--module-color': module.color }
    "
  >
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
          <input
            type="text"
            name="search"
            class="list-layout__header__actions__list__search"
            :placeholder="$t('modules.actions.search_placeholder')"
            v-model="search"
            @input="handleSearchInput"
            @keydown.enter.prevent="performSearch(1)"
          />

          <span
            @click="resetSearchValue()"
            :class="[
              'list-layout__header__actions__list__search-reseter',
              { 'hide-reseter': !search },
            ]"
            ><i class="fa-regular fa-circle-xmark"></i>
          </span>

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
              <li>
                <Link
                  class="list-layout__header__actions__list__dropdown__item"
                  :href="editModuleUrl"
                >
                  <i class="fa-solid fa-wrench"></i>
                  {{ $t("modules.actions.edit_module") }}
                </Link>
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
      :meta="meta"
      :allMatchingSelected="allMatchingSelected"
      @toggleAll="toggleAll()"
      @cancelClicked="resetActionZone()"
      @clearSelection="clearSelection()"
      @deleteClicked="handleListDelete()"
    />

    <MassUpdateZone
      v-else-if="showMassUpdateZone"
      :selected-ids="selectedIds"
      :meta="meta"
      :all-matching-selected="allMatchingSelected"
      :fields="fields"
      :filters="pageProps.filters ?? {}"
      @massUpdate="handleMassUpdate"
      @toggleAll="toggleAll()"
      @clearSelection="clearSelection()"
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
              @click.stop
            >
              <input
                type="checkbox"
                :checked="allSelected"
                @change="toggleAllInView"
              />
            </th>

            <th
              v-for="col in listLayoutColumns || []"
              :key="col?.name"
              scope="col"
              :class="{ sortable: col?.sortable }"
              @click="sortBy(col)"
            >
              <span class="th-label">
                {{ $t(col.label) }}
                <i
                  v-if="col?.sortable"
                  :class="sortIcon(col)"
                  class="sort-icon"
                ></i>
              </span>
            </th>
          </tr>
        </thead>

        <tbody>
          <template v-if="meta && meta.total != 0">
            <Link
              v-for="item in sortedItems"
              :key="item.id"
              as="tr"
              class="clickable-row"
              :href="`/${module.slug}/${item.id}`"
            >
              <td
                v-if="bulkActionmode"
                @click.stop
                class="list-layout__table__bulk-select"
              >
                <input
                  type="checkbox"
                  :checked="isSelected(item.id) || allMatchingSelected"
                  @click.stop
                  @change="toggleRow(item.id)"
                />
              </td>

              <td v-for="col in listLayoutColumns || []" :key="col.name">
                <template v-if="col?.name === 'email' && item[col?.name]">
                  <a :href="'mailto:' + item[col.name]">
                    <span v-html="highlightMatch(item[col.key])"></span>
                  </a>
                </template>

                <template v-else-if="col.type === 'datetime' && item[col.name]">
                  {{ formatDateTime(item[col.name], appSettings) }}
                </template>
                <template v-else-if="col.type === 'date' && item[col.name]">
                  {{ formatDate(item[col.name], appSettings) }}
                </template>
                <template v-else-if="col.type === 'dropdown' && item[col.name]">
                  {{ getDropDownListLabel(col.name, item[col.name]) }}
                </template>
                <template
                  v-else-if="item[col.name] && item[col.name].length > 62"
                >
                  <span
                    v-html="
                      highlightMatch(item[col.name].substring(0, 64) + '...')
                    "
                  ></span>
                </template>

                <template v-else>
                  <span v-html="highlightMatch(item[col.name] ?? '-')"></span>
                </template>
              </td>
            </Link>
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
    <div class="list-layout__pagination">
      <Pagination v-if="meta && meta.total != 0" :meta="meta" />
    </div>
  </div>
</template>
