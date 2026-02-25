<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import { formatDateTime } from "@/utils/datetime";
import { useAlerts } from "@/Composables/useAlerts";

const { success, error, info, warning, removeAlert, clearAllAlerts } =
  useAlerts();
const props = defineProps({
  record: {
    type: Object,
    required: true,
  },
  parent: {
    type: Object,
  },
});

const pageData = usePage();
const appSettings = pageData.props.appSettings;
const modules = computed(() => pageData.props.modules);
const relationshipName = props.parent.name || null;
const currentModule = pageData.props.module.slug;
const currentRecordId = props.record?.id;

const emit = defineEmits(["close", "saved"]);

const isOpen = ref(true);

const closeOverlay = () => {
  isOpen.value = false;
};

const handleAfterLeave = () => {
  emit("close");
};

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
loadRecords();
</script>

<template>
  <Transition name="slide-right" appear @after-leave="handleAfterLeave">
    <div
      v-if="isOpen"
      class="record-overlay"
      @click.self="closeOverlay"
      :style="{
        '--related-color': getRelatedColor(parent.related_slug),
      }"
    >
      <div class="related-links" ref="overlayRef">
        <div class="related-links__header">
          <div class="related-links__header__title">
            {{ $t("Link to Parent") }}
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
        <!-- List -->
        <div class="related-links__list">
          <div class="related-links__modifiers">
            <h6>Showing {{ records?.length ?? "0" }} records</h6>
            <div class="related-links__modifiers__search">
              <input v-model="search" type="text" placeholder="Search..." />
              <span class="related-links__modifiers__search__clear">
                <i class="fa-solid fa-xmark" v-if="search.length"></i>
              </span>
            </div>
          </div>

          <table
            v-if="cleanedLayout && cleanedLayout.length"
            class="related-links__table"
          >
            <!-- HEADER -->
            <thead>
              <tr>
                <th class="related-links__head__space"></th>
                <th v-for="field in cleanedLayout" :key="field.name">
                  {{ $t(field.label) }}
                </th>
              </tr>
            </thead>

            <!-- LOADING -->
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

            <!-- RECORDS -->
            <tbody v-else>
              <tr
                v-for="record in records"
                :key="record.id"
                class="related-links__record"
                @click="toggleRow(record.id)"
                :class="{ selected: selected.includes(record.id) }"
              >
                <!-- Checkbox -->
                <td class="related-links__record__checkbox">
                  <Selectbox
                    @click="handleCheckBoxClick"
                    :value="record.id"
                    v-model="selected"
                    :color="getRelatedColor(panel.relationship.related_slug)"
                  />
                </td>

                <!-- Fields -->
                <td
                  v-for="field in cleanedLayout"
                  :key="field.name"
                  class="related-links__cell"
                >
                  <template v-if="field.name === 'name'">
                    <span
                      class="related-links__record-title related-links__record__field"
                    >
                      {{ formatField(field, record[field.name]) }}
                    </span>
                  </template>

                  <template v-else>
                    <span class="related-links__record__field">
                      {{ formatField(field, record[field.name]) }}
                    </span>
                  </template>
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
      </div>
    </div>
  </Transition>
</template>
