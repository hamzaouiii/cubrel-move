<script setup>
import { ref, onMounted, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
// import axios from "axios";
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
const loading = ref(true);
const records = ref([]);

onMounted(() => {
  setTimeout(() => {
    records.value = Array.from({ length: 180 }).map((_, index) => ({
      id: index + 1,
      name: `Lead ${index + 1}`,
      subtitle: `Client Company ${index + 1} • Active`,
    }));

    loading.value = false;
  }, 3000);
});
const page = usePage();
const appSettings = page.props.appSettings;

const modules = computed(() => page.props.modules);

const getModule = (slug) => modules.value.find((m) => m.slug === slug);

const getRelatedColor = (slug) => {
  return appSettings.use_individual_module_colors == "0"
    ? appSettings.primary_color
    : getModule(slug)?.color;
};

const selected = ref([]);
const save = () => {
  console.log("Selected record IDs:", selected.value);

  emit("save", selected.value);
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
