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
import { formatDateTime } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";

import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Pages/Components/Pagination.vue";
import ListDeleteZone from "../Components/ListDeleteZone.vue";
import MassUpdateZone from "../Components/MassUpdateZone.vue";

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
    selectedIds.value.includes(i.id)
  );

  selectedIds.value = allInViewSelected
    ? selectedIds.value.filter(
        (id) => !pageProps.items.some((i) => i.id === id)
      )
    : Array.from(
        new Set([...selectedIds.value, ...pageProps.items.map((i) => i.id)])
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
  }
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
    }
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
  const base = "settings/ /modules/";
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
    ? pageProps.meta?.total ?? 0
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
    "Mass update will not be sent to the backend until field types are introduced "
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
</script>

<template>
  <Head>
    <title>{{ title }}</title>
  </Head>

  <div class="ar-main-container">
    <div class="ar-main-container_header">
      <div class="ar-main-container_header_details">
        <h1 class="ar-main-container_header_details_title">
          {{ $t(module.label) }}
        </h1>
        <span class="ar-main-container_header_details_meta">{{
          recordsNumberPhrase
        }}</span>
      </div>

      <div class="ar-main-container_header_actions" ref="actionDropDownref">
        <div
          class="input-group actions_container"
          :style="
            appSettings.use_individual_module_colors == '0'
              ? { '--module-color': appSettings.primary_color }
              : { '--module-color': module.color }
          "
        >
          <input
            type="text"
            name="search"
            class="search-input"
            aria-label="Text input with segmented dropdown button"
            :placeholder="$t('modules.actions.search_placeholder')"
            v-model="search"
            @input="handleSearchInput"
            @keydown.enter.prevent="performSearch(1)"
          />

          <span
            @click="resetSearchValue()"
            :class="['search-reseter', { 'hide-reseter': !search }]"
            ><i class="fa-regular fa-circle-xmark"></i>
          </span>

          <button type="button" class="main-btn" @click="goToCreateView()">
            {{ $t("modules.actions.create") }}
          </button>

          <button
            @click="toggleActionDropDown"
            type="button"
            class="dropdown-btn"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i
              :class="
                showActionDropDown
                  ? 'fa-solid fa-chevron-up'
                  : 'fa-solid fa-chevron-down'
              "
            ></i>
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>

          <transition name="fade">
            <ul
              v-if="showActionDropDown"
              class="ar-dropdown ar-dropdown-end show"
            >
              <li>
                <Link class="ar-dropdown-item" :href="editModuleUrl">
                  <i class="fa-solid fa-wrench"></i>
                  {{ $t("modules.actions.edit_module") }}
                </Link>
              </li>
              <li>
                <span class="ar-dropdown-item" @click="toggleMassUpdateZone()">
                  <i class="fa-solid fa-square-pen"></i>
                  {{ $t("modules.actions.mass_update") }}
                </span>
              </li>
              <li><hr class="ar-dropdown-divider" /></li>
              <li>
                <span
                  class="ar-dropdown-item ar-dropdown-item-delete"
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

    <div
      class="ar-main-container_content"
      :style="
        appSettings.use_individual_module_colors == '0'
          ? { '--module-color': appSettings.primary_color }
          : { '--module-color': module.color }
      "
    >
      <div>
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
        <table class="ar-main-container_content_table">
          <thead>
            <tr>
              <th v-if="bulkActionmode" scope="col" class="bulk-select-col">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleAllInView"
                />
              </th>

              <th
                v-for="col in listLayout?.columns || []"
                :key="col.key"
                scope="col"
              >
                {{ $t(col.label) }}
              </th>
            </tr>
          </thead>

          <tbody>
            <template v-if="meta && meta.total != 0">
              <Link
                v-for="item in items"
                :key="item.id"
                as="tr"
                class="clickable-row"
                :href="`/${module.slug}/${item.id}`"
              >
                <td v-if="bulkActionmode" class="bulk-select-col">
                  <input
                    type="checkbox"
                    :checked="isSelected(item.id) || allMatchingSelected"
                    @click.stop
                    @change="toggleRow(item.id)"
                  />
                </td>

                <td v-for="col in listLayout?.columns || []" :key="col.key">
                  <template v-if="col.key === 'email' && item[col.key]">
                    <a :href="'mailto:' + item[col.key]">
                      <span v-html="highlightMatch(item[col.key])"></span>
                    </a>
                  </template>

                  <template
                    v-else-if="col.type === 'datetime' && item[col.key]"
                  >
                    {{ formatDateTime(item[col.key], appSettings) }}
                  </template>

                  <template
                    v-else-if="item[col.key] && item[col.key].length > 62"
                  >
                    <span
                      v-html="
                        highlightMatch(item[col.key].substring(0, 64) + '...')
                      "
                    ></span>
                  </template>

                  <template v-else>
                    <span v-html="highlightMatch(item[col.key] ?? '-')"></span>
                  </template>
                </td>
              </Link>
            </template>

            <template v-else>
              <tr class="no-data-row">
                <td
                  :colspan="
                    (listLayout?.columns?.length ?? 0) +
                    (bulkActionmode ? 1 : 0)
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

      <Pagination v-if="meta && meta.total != 0" :meta="meta" />
    </div>
  </div>
</template>
