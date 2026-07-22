<script setup>
import { ref, computed, watch, nextTick, getCurrentInstance } from "vue";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import axios from "axios";

const props = defineProps({
  open: { type: Boolean, default: false },
  searchEndpoint: { type: String, required: true },
  relatedModule: { type: String, default: null },
  layout: { type: Array, default: () => [] },
  fields: { type: Array, default: () => [] },
  accentColor: { type: String, default: "var(--module-color)" },
  excludeIds: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "select"]);

const { proxy } = getCurrentInstance();
const t = proxy.$t;

const isOpen = ref(props.open);
const closeOverlay = () => {
  isOpen.value = false;
};
const handleAfterLeave = () => emit("close");

const query = ref("");
const records = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const searchInput = ref(null);

const selectedIds = ref([]);
const selectedMap = ref({});

const singleLabel = computed(() =>
  t("modules." + props.relatedModule + ".single_label"),
);
const pluralLabel = computed(() =>
  t("modules." + props.relatedModule + ".label"),
);
const drawerTitle = computed(
  () => `${t("modules.selectdrawer.select")} ${pluralLabel.value}`,
);
const confirmLabel = computed(() =>
  selectedIds.value.length
    ? t("modules.selectdrawer.add_count", { count: selectedIds.value.length })
    : t("modules.selectdrawer.add"),
);

const layoutColumns = computed(() => Object.values(props.layout ?? {}));
const primaryColumn = computed(
  () =>
    layoutColumns.value[0] ?? { name: "name", label: "modules.defaults.name" },
);
const extraColumns = computed(() =>
  layoutColumns.value
    .slice(1)
    .filter((col) => props.fields?.some((field) => field.name === col.name)),
);
const getField = (col) =>
  props.fields?.find((field) => field.name === col.name) ?? col;

let debounceTimer = null;

const fetchRecords = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await axios.get(props.searchEndpoint, {
      params: { q: query.value, page },
    });
    records.value = (data.data || []).filter(
      (r) => !props.excludeIds.includes(r.id),
    );
    currentPage.value = data.current_page;
    lastPage.value = data.last_page;
    total.value = data.total;
  } catch (e) {
    console.error("RecordMultiSelectorDrawer fetch error", e.message);
  } finally {
    loading.value = false;
  }
};

const onQueryInput = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchRecords(1), 400);
};

const clearSearch = () => {
  query.value = "";
  fetchRecords(1);
};

const nextPage = () => {
  if (currentPage.value < lastPage.value) fetchRecords(currentPage.value + 1);
};
const prevPage = () => {
  if (currentPage.value > 1) fetchRecords(currentPage.value - 1);
};

watch(
  () => props.open,
  async (val) => {
    isOpen.value = val;
    if (val) {
      query.value = "";
      selectedIds.value = [];
      selectedMap.value = {};
      records.value = [];
      await fetchRecords(1);
      await nextTick();
      searchInput.value?.focus();
    }
  },
);

const toggleRow = (record) => {
  const idx = selectedIds.value.indexOf(record.id);
  if (idx === -1) {
    selectedIds.value.push(record.id);
    selectedMap.value[record.id] = record;
  } else {
    selectedIds.value.splice(idx, 1);
    delete selectedMap.value[record.id];
  }
};

const allSelected = computed(
  () =>
    records.value.length > 0 &&
    records.value.every((r) => selectedIds.value.includes(r.id)),
);

const toggleSelectAll = () => {
  if (allSelected.value) {
    records.value.forEach((r) => toggleRow(r));
  } else {
    records.value.forEach((r) => {
      if (!selectedIds.value.includes(r.id)) toggleRow(r);
    });
  }
};

const confirm = () => {
  if (!selectedIds.value.length) return;
  emit(
    "select",
    selectedIds.value.map((id) => selectedMap.value[id]),
  );
  closeOverlay();
};
</script>

<template>
  <Transition name="slide-right" appear @after-leave="handleAfterLeave">
    <div
      v-if="isOpen"
      class="record-overlay"
      :style="{ '--related-color': accentColor }"
      role="dialog"
      aria-modal="true"
      @click.self="closeOverlay"
    >
      <div class="related-links related-links--compact">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{ drawerTitle }}
          </div>
          <button class="related-links__header__close" @click="closeOverlay">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="related-links__toolbar">
          <span class="related-links__toolbar__info">
            {{ records?.length ?? "0" }} {{ $t("modules.of") }}
            {{ total ?? "0" }}
          </span>
          <div class="related-links__toolbar__search">
            <input
              ref="searchInput"
              v-model="query"
              type="text"
              :placeholder="$t('modules.linking.search')"
              @input="onQueryInput"
            />
            <span
              class="related-links__toolbar__search__clear"
              @click.stop="clearSearch"
            >
              <i class="fa-solid fa-xmark" v-if="query.length"></i>
            </span>
          </div>
        </div>

        <div class="related-links__list">
          <div
            v-if="!loading && (!records || records.length === 0)"
            class="related-links__no-records"
          >
            <i class="fa-solid fa-border-none"></i>
            <span>{{ $t("modules.linking.no_records_found") }}</span>
          </div>

          <table v-else class="related-links__table">
            <thead>
              <tr>
                <th class="related-links__head__space">
                  <Selectbox
                    :model-value="allSelected"
                    @update:model-value="toggleSelectAll"
                    :color="accentColor"
                  />
                </th>
                <th>{{ $t(primaryColumn.label) }}</th>
                <th v-for="col in extraColumns" :key="col.name">
                  {{ $t(col.label) }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="record in records"
                :key="record.id"
                class="related-links__record"
                :class="{ selected: selectedIds.includes(record.id) }"
                @click="toggleRow(record)"
              >
                <td class="related-links__record__checkbox" @click.stop>
                  <Selectbox
                    :model-value="selectedIds"
                    :value="record.id"
                    @update:model-value="() => toggleRow(record)"
                    :color="accentColor"
                  />
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
                      :highlight="query"
                    />
                  </span>
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
            <span>{{ currentPage }} {{ $t("modules.of") }} {{ lastPage }}</span>
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

        <div class="related-links__footer">
          <button
            class="related-links__footer__btn related-links__footer__btn--cancel"
            @click="closeOverlay"
          >
            {{ $t("modules.actions.cancel") }}
          </button>
          <button
            class="related-links__footer__btn related-links__footer__btn--primary"
            :disabled="!selectedIds.length"
            @click="confirm"
          >
            {{ confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
