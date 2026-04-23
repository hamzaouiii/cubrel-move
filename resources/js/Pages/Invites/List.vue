<script setup>
import {
  computed,
  ref,
  onMounted,
  onBeforeUnmount,
  nextTick,
  getCurrentInstance,
} from "vue";
import { Head, usePage, Link, useForm, router } from "@inertiajs/vue3";
import { useAlerts } from "@/Composables/useAlerts";
import { useConfirm } from "@/Composables/useConfirm";
import FieldRenderer from "../Components/Globals/FieldRenderer.vue";
import Layout from "@/Layouts/Layout.vue";
import Pagination from "@/Pages/Components/Globals/Pagination.vue";
import InviteModal from "@/Pages/Components/Users/InviteModal.vue";
const { success, error, info, clearAllAlerts } = useAlerts();
const { confirm } = useConfirm();

defineOptions({
  layout: Layout,
});

const props = defineProps({
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
const appSettings = usePage().props.appSettings;

const showListSearch = ref(false);
const search = ref(props.filters.search ?? "");
const searchInput = ref(null);
const sortKey = ref(null);
const sortDir = ref("asc");

const showInviteModal = ref(false);

const listLayoutColumns = computed(() => {
  return Object.values(props.listLayout?.columns || {}).filter(
    (column) => column !== null,
  );
});

const sortedItems = computed(() => {
  if (!sortKey.value) return props.items;

  return [...props.items].sort((a, b) => {
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

const module_color = computed(() => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : props.module.color;
});

const recordsNumberPhrase = computed(() => {
  if (!props.meta) return "(0)";
  return `${props.items?.length ?? 0} ${t("modules.of")} ${props.meta.total}`;
});

const getField = (item) => {
  const field = Object.values(props.fields)?.find(
    (field) => field.name === item.name,
  );
  return field;
};

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
    performSearch(1);
  }
};

const resetSearchValue = () => {
  search.value = "";
  handleSearchInput();
};

const openInviteModal = () => {
  showInviteModal.value = true;
};

const closeInviteModal = () => {
  showInviteModal.value = false;
  router.reload({
    only: ["items", "meta"],
  });
};

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

const hidePagination = computed(() => {
  return props.meta?.total < props.meta?.perPage;
});

const handleResend = async (item) => {
  const confirmed = await confirm({
    title: t("modules.userinvites.actions.resend"),
    message: `${t("modules.userinvites.confirm.resend")} ${item.email}?`,
    highlight: item.email,
  });
  if (!confirmed) return;

  router.post(
    `/invites/${item.id}/resend`,
    {},
    {
      preserveScroll: true,
      onSuccess: () => success(t("modules.userinvites.alerts.resent")),
      onError: () => error(t("modules.userinvites.alerts.resend_failed")),
    },
  );
};

const handleRevoke = async (item) => {
  const confirmed = await confirm({
    title: t("modules.userinvites.actions.revoke"),
    message: `${t("modules.userinvites.confirm.revoke")} ${item.email}?`,
    danger: true,
    highlight: item.email,
  });
  if (!confirmed) return;

  router.patch(
    `/invites/${item.id}/revoke`,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ["items"] });
        success(t("modules.userinvites.alerts.revoked"));
      },
      onError: () => error(t("modules.userinvites.alerts.revoke_failed")),
    },
  );
};

const handleDelete = async (item) => {
  const confirmed = await confirm({
    title: t("modules.userinvites.actions.delete"),
    message: `${t("modules.userinvites.confirm.delete")} ${item.email}?`,
    danger: true,
    highlight: item.email,
  });
  if (!confirmed) return;

  router.delete(`/invites/${item.id}`, {
    preserveScroll: true,
    onSuccess: () => success(t("modules.userinvites.alerts.deleted")),
    onError: () => error(t("modules.userinvites.alerts.delete_failed")),
  });
};
</script>

<template>
  <Head>
    <title>{{ title }} - Cubrel</title>
  </Head>

  <div class="list-layout" :style="{ '--module-color': module_color }">
    <div class="list-layout__header">
      <div class="list-layout__header__details">
        <span class="list-layout__header__details__title">
          {{ $t(module.label) }}
        </span>
        <span class="list-layout__header__details__meta">{{
          recordsNumberPhrase
        }}</span>
      </div>

      <div class="list-layout__header__actions">
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
                :placeholder="$t('modules.actions.search_placeholder_by_email')"
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
          <button @click="openInviteModal()">
            <i class="fa-solid fa-plus"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="list-layout__table-scroll">
      <table class="list-layout__table">
        <thead>
          <tr>
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
            <th></th>
          </tr>
        </thead>

        <tbody>
          <template v-if="meta && meta.total != 0">
            <tr
              v-for="item in sortedItems"
              :key="item.id"
              style="cursor: default !important"
            >
              <td v-for="col in listLayoutColumns || []" :key="col.name">
                <FieldRenderer
                  :field="getField(col)"
                  v-model="item[col.name]"
                  mode="table"
                  :module-color="module_color"
                  :highlight="search"
                  :related_label="item[col.name + '__label'] ?? null"
                />
              </td>
              <td class="row-actions" @click.stop>
                <button
                  class="row-action-btn row-action-btn--resend"
                  @click="handleResend(item)"
                  :class="{
                    'row-action-btn--disabled':
                      item.status === 'accepted' || item.status === 'revoked',
                  }"
                  :title="$t('modules.userinvites.actions.resend')"
                >
                  <i class="fa-solid fa-paper-plane"></i>

                  {{ $t("modules.userinvites.actions.resend") }}
                </button>

                <button
                  class="row-action-btn row-action-btn--revoke"
                  @click="handleRevoke(item)"
                  :title="$t('modules.userinvites.actions.revoke')"
                  :class="{
                    'row-action-btn--disabled':
                      item.status === 'expired' ||
                      item.status === 'accepted' ||
                      item.status === 'revoked',
                  }"
                >
                  <i class="fa-solid fa-ban"></i>
                  {{ $t("modules.userinvites.actions.revoke") }}
                </button>

                <button
                  class="row-action-btn row-action-btn--delete"
                  @click="handleDelete(item)"
                  :title="$t('modules.userinvites.actions.delete')"
                >
                  <i class="fa-solid fa-trash"></i>

                  {{ $t("modules.userinvites.actions.delete") }}
                </button>
              </td>
            </tr>
          </template>

          <template v-else>
            <tr class="no-data-row">
              <td
                :colspan="listLayoutColumns?.length ?? 0"
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
  <InviteModal
    v-if="showInviteModal"
    @close="closeInviteModal()"
    :module="module"
  ></InviteModal>
</template>
