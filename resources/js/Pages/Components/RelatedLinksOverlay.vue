<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Selectbox from "./FiledTypes/Selectbox.vue";
import { formatDateTime } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";

const { success, error, info, warning, removeAlert, clearAllAlerts } =
  useAlerts();
const props = defineProps({
  panel: {
    type: Object,
    required: true,
  },
  layout: {
    type: Object,
    required: true,
  },
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

const appSettings = pageData.props.appSettings;
const modules = computed(() => pageData.props.modules);

const currentModule = pageData.props.module.slug;
const currentRecordId = pageData.props.record?.id;
const relationshipName = props.panel?.relationship?.name || null;

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
    console.error("Missing relationship context");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.get(
      `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}/available`,
      {
        params: {
          page: page.value,
          per_page: perPage,
          search: search.value,
        },
      },
    );

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

    // Optional: clear selection after success
    selected.value = [];
    clearAllAlerts();
    success("Linking records finished successfully ");
    emit("saved");
    closeOverlay();
  } catch (error) {
    console.error(
      "Failed saving related records:",
      error.response?.data || error.message,
    );
  } finally {
    saveLoading.value = false;
  }
};

const formatField = (field, value) => {
  if (value == null || value === "") return "";

  const type = field?.type?.toLowerCase();

  switch (type) {
    case "textfield":
      return value;

    case "datetime":
      return formatDateTime(value);

    case "longtext":
      return value.length > 34 ? value.slice(0, 44) + "…" : value;

    default:
      return value;
  }
};
</script>

<template>
  <Transition name="slide-right" appear @after-leave="handleAfterLeave">
    <div
      v-if="isOpen"
      class="record-overlay"
      @click.self="closeOverlay"
      :style="{
        '--related-color': getRelatedColor(panel.relationship.related_slug),
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
          <!-- List -->
          <div class="related-links__list">
            <div class="related-links__modifiers">
              <h6>Showing {{ records.length }} records</h6>
              <div class="related-links__modifiers__search">
                <input v-model="search" type="text" placeholder="Search..." />
                <span class="related-links__modifiers__search__clear">
                  <i class="fa-solid fa-xmark" v-if="search.length"></i>
                </span>
              </div>
            </div>
            <ul
              v-if="cleanedLayout && cleanedLayout.length"
              class="related-links__head"
            >
              <li class="related-links__head__space"></li>
              <li v-for="field in cleanedLayout" :key="field.name">
                {{ $t(field.label) }}
              </li>
            </ul>
            <template v-if="loading">
              <div v-for="n in 15" :key="'related-links__skeleton-' + n">
                <ul class="related-links__record related-links__skeleton">
                  <li class="skeleton-checkbox"></li>

                  <li
                    v-for="field in cleanedLayout"
                    :key="field.name"
                    class="skeleton skeleton-item"
                  ></li>
                </ul>
              </div>
            </template>
            <template v-else>
              <ul
                class="related-links__record"
                v-for="record in records"
                :key="record.id"
              >
                <label>
                  <li class="related-links__record__checkbox">
                    <Selectbox
                      :value="record.id"
                      v-model="selected"
                      :color="getRelatedColor(panel.relationship.related_slug)"
                    />
                  </li>

                  <li
                    v-for="field in cleanedLayout"
                    :key="field.name"
                    class="related-links__cell"
                  >
                    <template v-if="field.name === 'name'"
                      ><span
                        class="related-links__record-title related-links__record__field"
                      >
                        {{ formatField(field, record[field.name]) }}
                      </span></template
                    >
                    <template v-else
                      ><span class="related-links__record__field">
                        {{ formatField(field, record[field.name]) }}
                      </span></template
                    >
                  </li>
                </label>
              </ul>
            </template>
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
