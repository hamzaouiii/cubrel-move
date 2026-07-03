<script setup>
import { ref, onMounted, computed, watch, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import { useAlerts } from "@/Composables/useAlerts";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";
import QuickCreateRecordModal from "./QuickCreateRecordModal.vue";

const { success, error, info, warning, clearAllAlerts } = useAlerts();
const props = defineProps({
  panel: {
    type: Object,
    required: true,
  },
  layout: {
    type: Object,
    required: true,
  },
  selectedParent: Object,
  relationship: Object,
});
const cleanedLayout = computed(() => {
  if (!props.layout) return [];

  const values = Array.isArray(props.layout)
    ? props.layout
    : Object.values(props.layout);

  return values.filter((field) => field && field.name);
});

const emit = defineEmits(["close", "saved"]);

const isOpen = ref(true);

const closeOverlay = () => {
  isOpen.value = false;
};

const handleAfterLeave = () => {
  emit("close");
};
const pageData = usePage();
const { proxy } = getCurrentInstance();
const t = proxy.$t;
const appSettings = pageData.props.appSettings;
const modules = computed(() => pageData.props.modules);

const currentModule = pageData.props.module.slug;
const currentRecordId = pageData.props.record?.id;
const relationshipName = props.relationship?.name || null;

const saveLoading = ref(false);
const loading = ref(false);
const records = ref([]);
const selected = ref([]);

const page = ref(1);
const lastPage = ref(1);
const total = ref(0);

const search = ref("");
let searchTimeout = null;

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedColor = (slug) => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;
};

const getIcon = (slug) => {
  if (!slug) return "fa-solid fa-user";
  return getModule(slug)?.icon || "fa-solid fa-user";
};

const relatedFieldsArray = computed(() =>
  Object.values(props.relationship?.fields || {}),
);

const quickCreateOpen = ref(false);

// Purely presentational — drives the "New <label>" / "Link :count <label>" text.
const singleLabel = computed(() =>
  t("modules." + props.relationship.related_slug + ".single_label"),
);
const pluralLabel = computed(() =>
  t("modules." + props.relationship.related_slug + ".label"),
);
const selectedLabel = computed(() =>
  selected.value.length === 1 ? singleLabel.value : pluralLabel.value,
);

const onQuickCreated = (record) => {
  quickCreateOpen.value = false;
  records.value = [record, ...records.value.filter((r) => r.id !== record.id)];
  if (!selected.value.includes(record.id)) {
    selected.value = [record.id, ...selected.value];
  }
  total.value = (total.value ?? 0) + 1;
};

const loadRecords = async () => {
  if (!relationshipName || !currentModule || !currentRecordId) {
    error(t("modules.linking.error_missing_context"));
    return;
  }
  const url = `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}/available`;
  loading.value = true;
  try {
    const response = await axios.get(url, {
      params: {
        page: page.value,
        search: search.value,
      },
    });

    records.value = response.data.data;
    page.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    total.value = response.data.total;
  } catch (err) {
    console.error(
      "Failed loading available records:",
      err.response?.data || err.message,
    );
    error(t("modules.linking.error_lodaing_related_records"));
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadRecords();
});

// Debounced search
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    page.value = 1;
    loadRecords();
  }, 400);
});

const nextPage = () => {
  if (page.value < lastPage.value) {
    page.value++;
    loadRecords();
  }
};

const prevPage = () => {
  if (page.value > 1) {
    page.value--;
    loadRecords();
  }
};

const save = async () => {
  if (!relationshipName || !currentModule || !currentRecordId) {
    error("Missing relationship context");
    return;
  }

  if (!selected.value.length) {
    warning("No records selected");
    return;
  }

  saveLoading.value = true;
  try {
    info(t("modules.linking.info_linking"));
    await axios.post(
      `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}`,
      {
        related_ids: selected.value,
      },
    );

    selected.value = [];
    clearAllAlerts();
    success(t("modules.linking.success"));
    emit("saved", props.panel.name);
    closeOverlay();
  } catch (e) {
    console.error(
      "Failed saving related records:",
      e.response?.data || e.message,
    );
    error(e.response?.data?.message);
  } finally {
    saveLoading.value = false;
  }
};

const displayedRecords = computed(() => {
  if (!props.selectedParent || props.relationship.role === "parent") {
    return records.value;
  }

  const selectedId = props.selectedParent.id;

  // Remove from paginated results if it exists there
  const filtered = records.value.filter((r) => r.id !== selectedId);

  // Put selected record at top
  return [props.selectedParent, ...filtered];
});

const toggleRow = (id) => {
  const index = selected.value.indexOf(id);
  if (index === -1) {
    selected.value.push(id);
  } else {
    selected.value.splice(index, 1);
  }
};

const initializeSelected = () => {
  if (
    props.selectedParent &&
    selected.value.length === 0 &&
    props.relationship.type != "many-to-many"
  ) {
    selected.value = [props.selectedParent.id];
  }
};

onMounted(() => {
  initializeSelected();
});

watch(
  () => props.selectedParent,
  (newVal) => {
    if (newVal) {
      selected.value = [newVal];
    }
  },
);

const allSelected = computed(() => {
  if (!displayedRecords.value.length) return false;

  return displayedRecords.value.every((r) => selected.value.includes(r.id));
});

const toggleSelectAll = () => {
  if (allSelected.value) {
    selected.value = [];
  } else {
    selected.value = displayedRecords.value.map((r) => r.id);
  }
};

const getField = (item) => {
  return Object.values(props.relationship?.fields)?.find(
    (field) => field.name === item.name,
  );
};

const clearSearch = () => {
  search.value = "";
};
</script>

<template>
  <Transition name="slide-right" appear @after-leave="handleAfterLeave">
    <div
      v-if="isOpen"
      class="record-overlay"
      @click.self="closeOverlay"
      :style="{
        '--related-color': getRelatedColor(relationship.related_slug),
      }"
    >
      <div class="related-links related-links--compact" ref="overlayRef">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{ $t("modules.linking.link_existing_records") }}
          </div>

          <button
            class="related-links__header__close"
            :aria-label="$t('modules.linking.close')"
            @click="closeOverlay"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <template v-if="saveLoading">
          <div class="saving-loader">
            <div class="lds-ripple">
              <div></div>
              <div></div>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="related-links__toolbar">
            <span class="related-links__toolbar__info">
              {{ records?.length ?? "0" }}
              {{ $t("modules.of") }} {{ total ?? "0" }}
            </span>

            <div class="related-links__toolbar__search">
              <input
                v-model="search"
                type="text"
                :placeholder="$t('modules.linking.search')"
              />
              <span
                class="related-links__toolbar__search__clear"
                @click.stop="clearSearch"
              >
                <i class="fa-solid fa-xmark" v-if="search.length"></i>
              </span>
            </div>

            <button
              class="related-links__toolbar__create"
              @click="quickCreateOpen = true"
            >
              <i class="fa-solid fa-plus"></i>
              {{ $t("modules.linking.new_record", { label: singleLabel }) }}
            </button>
          </div>

          <div class="related-links__list">
            <table
              v-if="cleanedLayout && cleanedLayout.length"
              class="related-links__table"
            >
              <thead>
                <tr>
                  <th class="related-links__head__space">
                    <Selectbox
                      :value="'all'"
                      :modelValue="allSelected ? ['all'] : []"
                      @update:modelValue="toggleSelectAll"
                      :color="getRelatedColor(relationship.related_slug)"
                    />
                  </th>
                  <th v-for="field in cleanedLayout" :key="field.name">
                    {{ $t(field.label) }}
                  </th>
                </tr>
              </thead>

              <tbody v-if="loading">
                <tr
                  v-for="n in 25"
                  :key="'related-links__skeleton-' + n"
                  class="related-links__skeleton"
                >
                  <td>
                    <span class="skeleton skeleton-checkbox"></span>
                  </td>
                  <td v-for="field in cleanedLayout" :key="field.name">
                    <span class="skeleton skeleton-item"></span>
                  </td>
                </tr>
              </tbody>

              <tbody v-else>
                <tr
                  v-for="record in displayedRecords"
                  :key="record.id"
                  class="related-links__record"
                  @click="toggleRow(record.id)"
                  :class="{ selected: selected.includes(record.id) }"
                >
                  <td class="related-links__record__checkbox">
                    <Selectbox
                      :value="record.id"
                      v-model="selected"
                      @update:modelValue="() => toggleRow(record.id)"
                      :color="getRelatedColor(relationship.related_slug)"
                    />
                  </td>

                  <td
                    v-for="field in cleanedLayout"
                    :key="field.name"
                    class="related-links__cell"
                  >
                    <span
                      :class="
                        ('related-links__record__field',
                        {
                          'related-links__record-title': field.name === 'name',
                        })
                      "
                    >
                      <FieldRenderer
                        :field="getField(field)"
                        v-model="record[field.name]"
                        :module-color="
                          getRelatedColor(relationship.related_slug)
                        "
                        mode="linkingPanel"
                        :highlight="search"
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
                'related-links__pagination__item--disabled': page === 1,
              }"
            >
              <span><i class="fa-solid fa-angle-left"></i></span>
            </li>
            <li class="related-links__pagination__item">
              <span>{{ page }} {{ $t("modules.of") }} {{ lastPage }}</span>
            </li>
            <li
              @click="nextPage"
              class="related-links__pagination__item"
              :class="{
                'related-links__pagination__item--disabled': page === lastPage,
              }"
            >
              <span>
                <i class="fa-solid fa-angle-right"></i>
              </span>
            </li>
          </ul>
        </template>

        <div class="related-links__footer">
          <button
            class="related-links__footer__btn related-links__footer__btn--cancel"
            @click="closeOverlay"
          >
            {{ $t("modules.actions.cancel") }}
          </button>
          <button
            class="related-links__footer__btn related-links__footer__btn--primary"
            :disabled="saveLoading || !selected.length"
            @click="save"
          >
            <i v-if="saveLoading" class="fa-solid fa-atom fa-spin"></i>
            {{
              selected.length
                ? $t("modules.linking.link_count", {
                    count: selected.length,
                    label: selectedLabel,
                  })
                : $t("modules.actions.link")
            }}
          </button>
        </div>
      </div>

      <QuickCreateRecordModal
        :open="quickCreateOpen"
        :module-slug="relationship.related_slug"
        :fields="relatedFieldsArray"
        :icon="getIcon(relationship.related_slug)"
        :accent-color="getRelatedColor(relationship.related_slug)"
        @close="quickCreateOpen = false"
        @created="onQuickCreated"
      />
    </div>
  </Transition>
</template>
