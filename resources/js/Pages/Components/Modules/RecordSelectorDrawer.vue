<script setup>
import { ref, watch, nextTick, computed, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { useAlerts } from "@/Composables/useAlerts";
import FieldRenderer from "../Globals/FieldRenderer.vue";
import QuickCreateRecordModal from "./QuickCreateRecordModal.vue";
const { success, error: showError, info, clearAllAlerts } = useAlerts();
const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  searchEndpoint: {
    type: String,
    required: true,
  },
  layout: {
    type: Array,
    default: () => [],
  },
  icon: {
    type: String,
    default: "fa-solid fa-user",
  },
  accentColor: {
    type: String,
    default: "var(--module-color)",
  },
  selectedRecord: {
    type: String,
  },
  activeField: {
    type: Object,
  },
  relatedModule: {
    type: String,
  },
  fields: Object,
  relationshipName: {
    type: String,
    default: null,
  },
  allowCreate: {
    type: Boolean,
    default: false,
  },
});
const emit = defineEmits(["select", "close", "saved"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;
const page = usePage();
const linking = ref(false);

const query = ref("");
const records = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const searchInput = ref(null);

const drawerTitle = computed(() => {
  return `${t("modules.selectdrawer.select")} ${t("modules." + props.relatedModule + ".single_label")}`;
});

const layoutColumns = computed(() => props.layout ?? []);

// The first column drives the primary label; remaining columns are supplementary.
const primaryColumn = computed(
  () => layoutColumns.value[0] ?? { name: "name", label: "Name" },
);
const extraColumns = computed(() => layoutColumns.value.slice(1));

let debounceTimer = null;

const fetchRecords = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get(props.searchEndpoint, {
      params: { q: query.value, page, selected: props.selectedRecord },
    });
    records.value = data.data;
    currentPage.value = data.current_page;
    lastPage.value = data.last_page;
    total.value = data.total;
  } catch (e) {
    console.error("RecordSelectorDrawer fetch error", e.message);
  } finally {
    loading.value = false;
  }
};

const onQueryInput = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchRecords(1);
  }, 400);
};

const nextPage = () => {
  if (currentPage.value < lastPage.value) {
    fetchRecords(currentPage.value + 1);
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    fetchRecords(currentPage.value - 1);
  }
};

const clearSearch = () => {
  query.value = "";
  fetchRecords(1);
};

watch(
  () => props.open,
  async (val) => {
    if (val) {
      query.value = "";
      records.value = [];
      await fetchRecords(1);
      await nextTick();
      searchInput.value?.focus();
    }
  },
);

const getField = (f) => {
  return props.fields?.find((field) => field.name === f.name) ?? f;
};

const selectRecord = async (record) => {
  if (!props.relationshipName) {
    emit("select", record);
    emit("close");
    return;
  }

  if (linking.value) return;

  linking.value = true;
  try {
    info(t("modules.linking.info_linking"));
    await axios.post(
      `/modules/${page.props.module.slug}/${page.props.record.id}/relationships/${props.relationshipName}`,
      { related_ids: [record.id] },
    );
    clearAllAlerts();
    success(t("modules.linking.success"));
    emit("saved", props.relationshipName);
  } catch (e) {
    console.error(
      "Failed saving related record:",
      e.response?.data || e.message,
    );
    clearAllAlerts();
    showError(e.response?.data?.message);
  } finally {
    linking.value = false;
  }
};

const close = () => emit("close");

// Offered in relationship-linking mode (relationshipName set) and wherever a
// caller explicitly opts in via allowCreate (e.g. the line-items product picker) —
// not for the plain filter-value picker, which also reuses this drawer.
const canCreate = computed(() => !!props.relationshipName || props.allowCreate);
const quickCreateOpen = ref(false);

const onQuickCreated = async (record) => {
  quickCreateOpen.value = false;
  await selectRecord(record);
};
</script>

<template>
  <Transition name="slide-right" appear @after-leave="close">
    <div
      v-if="open"
      class="record-overlay"
      :style="{ '--related-color': accentColor }"
      role="dialog"
      @click.self="close"
      aria-modal="true"
    >
      <div class="related-links">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{ drawerTitle }}
          </div>
          <div class="related-links__header__actions">
            <button
              class="related-links__header__actions__close"
              @click="close"
            >
              {{ $t("modules.linking.close") }}
            </button>
            <button
              v-if="canCreate"
              class="related-links__header__actions__close"
              @click="quickCreateOpen = true"
            >
              <i class="fa-solid fa-plus"></i>
            </button>
          </div>
        </div>

        <div class="related-links__list">
          <div class="related-links__modifiers">
            <span class="related-links__modifiers__info">
              {{ records?.length ?? "0" }}
              {{ $t("modules.of") }} {{ total ?? "0" }}
            </span>
            <div class="related-links__modifiers__search">
              <input
                ref="searchInput"
                v-model="query"
                type="text"
                :placeholder="$t ? $t('modules.linking.search') : 'Search...'"
                @input="onQueryInput"
              />
              <span
                class="related-links__modifiers__search__clear"
                @click.stop="clearSearch"
              >
                <i class="fa-solid fa-xmark" v-if="query.length"></i>
              </span>
            </div>
          </div>

          <div
            v-if="!loading && (!records || records.length === 0)"
            class="related-links__no-records"
          >
            <i class="fa-solid fa-border-none"></i>
            <span>No records found</span>
          </div>

          <table class="related-links__table" v-else>
            <thead>
              <tr>
                <th class="related-links__head__space"></th>
                <th>{{ $t(primaryColumn.label) }}</th>
                <th v-for="col in extraColumns" :key="col.name">
                  {{ $t(col.label) }}
                </th>
                <th style="width: 40px"></th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="record in records"
                :key="record.id"
                @click="selectRecord(record)"
                :class="{ selected: record.id === selectedRecord }"
                style="cursor: pointer"
              >
                <td
                  :class="{ 'record-selected': record.id === selectedRecord }"
                >
                  <i :class="[icon]" style="opacity: 0.6"></i>
                </td>

                <td>
                  <span
                    class="related-links__record__field related-links__record-title"
                  >
                    {{ record[primaryColumn.name] }}
                  </span>
                </td>

                <td
                  v-for="col in extraColumns"
                  :key="col.name"
                  class="related-links__cell"
                >
                  <span class="related-links__record__field">
                    <FieldRenderer
                      :field="getField(col)"
                      v-model="record[col.name]"
                      mode="linkingPanel"
                      :highlight="search"
                    />
                  </span>
                </td>

                <td
                  class="related-links__cell"
                  style="text-align: right; opacity: 0.4"
                >
                  <i class="fa-solid fa-chevron-right"></i>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <ul class="related-links__pagination" v-if="lastPage > 1">
          <li
            @click="prevPage"
            class="related-links__pagination__item"
            :class="{
              'related-links__pagination__item--disabled': currentPage === 1,
            }"
          >
            <span><i class="fa-solid fa-angle-left"></i></span>
          </li>
          <li class="related-links__pagination__item">
            <span>
              {{ currentPage }} {{ $t ? $t("modules.of") : "of" }}
              {{ lastPage }}
            </span>
          </li>
          <li
            @click="nextPage"
            class="related-links__pagination__item"
            :class="{
              'related-links__pagination__item--disabled':
                currentPage === lastPage,
            }"
          >
            <span><i class="fa-solid fa-angle-right"></i></span>
          </li>
        </ul>
      </div>

      <QuickCreateRecordModal
        v-if="canCreate"
        :open="quickCreateOpen"
        :module-slug="relatedModule"
        :fields="fields"
        :icon="icon"
        :accent-color="accentColor"
        @close="quickCreateOpen = false"
        @created="onQuickCreated"
      />
    </div>
  </Transition>
</template>
