<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import Radiobox from "../FiledTypes/Radiobox.vue";
import { formatDateTime, formatDate } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";
import FieldRenderer from "@/Pages/Components/Globals/FieldRenderer.vue";

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

const isSingleSelect = computed(() => {
  const role = props.relationship?.role;
  return role === "child" || role === "sibling";
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
const perPage = 25;

const search = ref("");
let searchTimeout = null;

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedColor = (slug) => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;
};

const loadRecords = async () => {
  if (!relationshipName || !currentModule || !currentRecordId) {
    error("Missing relationship context");
    return;
  }
  let url;
  loading.value = true;
  if (isSingleSelect.value) {
    url = `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}/single_link`;
  } else {
    url = `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}/available`;
  }
  try {
    const response = await axios.get(url, {
      params: {
        page: page.value,
        per_page: perPage,
        search: search.value,
      },
    });

    records.value = response.data.data;
    page.value = response.data.current_page;
    lastPage.value = response.data.last_page;
    total.value = response.data.total;
  } catch (error) {
    console.error(
      "Failed loading available records:",
      error.response?.data || error.message,
    );
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
    info("Saving");
    await axios.post(
      `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}`,
      {
        related_ids: selected.value,
      },
    );

    selected.value = [];
    clearAllAlerts();
    success("Linking records finished successfully ");
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
  if (isSingleSelect.value) {
    // Single select mode
    if (selected.value.includes(id)) {
      selected.value = [];
    } else {
      selected.value = [id];
    }
    return;
  }

  // Multi select mode (default behavior)
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

const selectedSingle = computed({
  get: () => selected.value[0] ?? null,
  set: (val) => {
    selected.value = val ? [val] : [];
  },
});

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
      <div class="related-links" ref="overlayRef">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{ $t("Link Existing Records") }}
          </div>

          <div class="related-links__header__actions">
            <button
              class="related-links__header__actions__btn"
              @click="closeOverlay"
            >
              {{ $t("Close") }}
            </button>
            <button class="related-links__header__actions__btn" @click="save">
              {{ $t("Save") }}
            </button>
          </div>
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
          <div class="related-links__list">
            <div class="related-links__modifiers">
              <span class="related-links__modifiers__info"
                >Showing {{ records?.length ?? "0" }} records</span
              >
              <div class="related-links__modifiers__search">
                <input v-model="search" type="text" placeholder="Search..." />
                <span
                  class="related-links__modifiers__search__clear"
                  @click.stop="clearSearch"
                >
                  <i class="fa-solid fa-xmark" v-if="search.length"></i>
                </span>
              </div>
            </div>

            <table
              v-if="cleanedLayout && cleanedLayout.length"
              class="related-links__table"
            >
              <thead>
                <tr>
                  <th class="related-links__head__space"></th>
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
                      v-if="!isSingleSelect"
                      :value="record.id"
                      v-model="selected"
                      @update:modelValue="() => toggleRow(record.id)"
                      :color="getRelatedColor(relationship.related_slug)"
                    />

                    <Radiobox
                      v-else
                      :value="record.id"
                      v-model="selectedSingle"
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
      </div>
    </div>
  </Transition>
</template>
