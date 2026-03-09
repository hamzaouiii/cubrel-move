<template>
  <div class="panel-footer">
    <div
      v-if="pagination && pagination.last_page > 1"
      class="panel-footer__pagination"
    >
      <div v-if="isLoading" class="panel-footer__loader">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>

      <button
        @click="paginate(pagination.prev_page_url)"
        :disabled="!pagination.prev_page_url || isLoading"
        class="pagination-btn"
        :class="{ 'is-disabled': !pagination.prev_page_url || isLoading }"
      >
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <span class="pagination-info">
        {{ pagination.current_page }} {{ $t("modules.of") }}
        {{ pagination.last_page }}
      </span>

      <button
        @click="paginate(pagination.next_page_url)"
        :disabled="!pagination.next_page_url || isLoading"
        class="pagination-btn"
        :class="{ 'is-disabled': !pagination.next_page_url || isLoading }"
      >
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>

    <div v-else></div>

    <div class="panel-footer__actions">
      <Link :href="`/${relatedSlug}`" class="view-all-link">
        {{ $t("modules.actions.view_all") }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
  pagination: Object,
  relatedSlug: String,
  relationshipName: String, // We need this for the 'only' partial reload
});

const isLoading = ref(false);
const emit = defineEmits(["loading"]);
const paginate = (url) => {
  if (!url) return;

  emit("loading", true); // Start skeleton

  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    only: ["record"],
    onFinish: () => {
      emit("loading", false); // Stop skeleton
    },
  });
};
</script>
