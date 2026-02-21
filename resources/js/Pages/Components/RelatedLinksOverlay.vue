<script setup>
import { ref, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  panel: Object,
});

const emit = defineEmits(["close"]);

const isOpen = ref(true);
const closeOverlay = () => {
  isOpen.value = false;
};

const handleAfterLeave = () => {
  emit("close");
};

const page = usePage();

const appSettings = page.props.appSettings;
const modules = computed(() => page.props.modules);

// 🔹 Current context (must exist on record page)
const currentModule = page.props.module.slug; // slug
const currentRecordId = page.props.record?.id; // id
const relationshipName = props.panel?.relationship?.name || null;

const loading = ref(false);
const records = ref([]);
const selected = ref([]);

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedColor = (slug) => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;
};

// 🔥 Load real data automatically
onMounted(async () => {
  if (!relationshipName || !currentModule || !currentRecordId) {
    console.error("Missing relationship context");
    return;
  }

  loading.value = true;

  try {
    const response = await axios.get(
      `/modules/${currentModule}/${currentRecordId}/relationships/${relationshipName}/available`,
    );

    records.value = response.data;
  } catch (error) {
    console.error(
      "Failed loading available records:",
      error.response?.data || error.message,
    );
  } finally {
    loading.value = false;
  }
});

// 🔹 Save now only logs selected ids
const save = () => {
  console.log("Selected IDs:", selected.value);
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

        <!-- List -->
        <div class="related-links__list">
          <template v-if="loading">
            <div
              v-for="n in 12"
              :key="'related-links__skeleton-' + n"
              class="related-links__row"
            >
              <div class="related-links__row-inner">
                <div class="skeleton-checkbox"></div>

                <div class="related-links__record">
                  <div class="related-links__skeleton skeleton-title"></div>
                  <div class="related-links__skeleton skeleton-subtitle"></div>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div
              v-for="record in records"
              :key="record.id"
              class="related-links__row"
            >
              <label class="related-links__row-inner">
                <input type="checkbox" :value="record.id" v-model="selected" />

                <div class="related-links__record">
                  <div class="related-links__record-title">
                    {{ record.name }}
                  </div>
                  <div
                    v-if="record.subtitle"
                    class="related-links__record-subtitle"
                  >
                    {{ record.subtitle }}
                  </div>
                </div>
              </label>
            </div>
          </template>
        </div>
      </div>
    </div>
  </Transition>
</template>
