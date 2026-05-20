<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { useDebounceFn } from "@/Composables/useDebounce";

const query = ref("");
const results = ref([]);
const loading = ref(false);
const searchInputRef = ref(null);
const open = ref(false);

// New state to track the last query that was ACTUALLY sent to the server
const lastSearchedQuery = ref("");

const emit = defineEmits(["onCloseOverlay"]);

const holdResults = ref(false);

const noResults = computed(() => {
  if (holdResults.value) return false;
  if (loading.value) return false;

  // Only show "No records found" if the current input matches what we actually searched for
  return (
    lastSearchedQuery.value.length >= 1 &&
    query.value === lastSearchedQuery.value &&
    Object.entries(results.value)?.length === 0
  );
});

// 1. The core search function (Runs immediately)
const executeSearch = async (q) => {
  if (q.length < 1) {
    results.value = [];
    lastSearchedQuery.value = "";
    loading.value = false;
    return;
  }

  loading.value = true;
  holdResults.value = true;
  try {
    const { data } = await axios.get("/search", { params: { q } });
    results.value = data?.results?.[0] || [];
    lastSearchedQuery.value = q; // Mark this specific string as searched
    open.value = true;
  } finally {
    loading.value = false;
    holdResults.value = false;
  }
};

// 2. The debounced wrapper (Only runs while typing)
const debouncedSearch = useDebounceFn((q) => {
  executeSearch(q);
}, 350);

// 3. The watcher handles the 4th-character auto-trigger
watch(query, (newVal) => {
  if (newVal.length >= 4) {
    loading.value = true;
    debouncedSearch(newVal);
  } else if (newVal.length === 0) {
    loading.value = false;
    results.value = [];
    lastSearchedQuery.value = "";
  }
  // If length is 1, 2, or 3, we just wait. The user must press Enter.
});

// 4. Triggered when the user explicitly hits the Enter key
const handleEnter = () => {
  if (query.value.length >= 1) {
    executeSearch(query.value);
  }
};

const goTo = (url) => {
  open.value = false;
  query.value = "";
  const absoluteUrl = url.startsWith("/") ? url : "/" + url;
  emit("onCloseOverlay");
  router.visit(absoluteUrl);
};

const getModule = (slug) => {
  if (!slug) return null;
  const module = usePage().props.modules.find((m) => m.slug === slug);
  return module;
};

const clearSearch = () => {
  query.value = "";
  results.value = [];
  lastSearchedQuery.value = "";
  loading.value = false;
  open.value = false;
};

onMounted(() => {
  searchInputRef.value?.focus();
});

const closeOverlayClicked = () => {
  emit("onCloseOverlay");
};
</script>
<template>
  <div class="search-overlay">
    <div class="search-overlay__close" @click="closeOverlayClicked">
      <i class="fa-solid fa-xmark"></i>
    </div>
    <div class="search-overlay__dialog">
      <div class="search-overlay__input-wrapper">
        <div class="search-overlay__glass-icon">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <input
          v-model="query"
          type="text"
          placeholder="Search"
          class="search-overlay__input"
          :class="{ 'search-overlay__input--loading': loading }"
          @keydown.esc="open = false"
          @keydown.enter="handleEnter"
          @focus="open = results.length > 0"
          ref="searchInputRef"
        />
        <div
          v-if="query.length"
          class="search-overlay__clear-btn"
          @click="clearSearch"
        >
          <i class="fa-solid fa-xmark"></i>
        </div>
        <div v-if="loading" class="search-overlay__spinner">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              stroke-dasharray="31.4 31.4"
            />
          </svg>
        </div>
      </div>

      <div
        v-if="query.length"
        class="search-overlay__results"
        ref="searchOverlayref"
      >
        <div
          v-for="(group, index) in results"
          :key="group.module"
          class="search-overlay__group"
        >
          <span
            class="search-overlay__group-label"
            :style="{
              '--module-color':
                getModule(index)?.color ||
                usePage().props.appSettings.primary_color,
            }"
          >
            <i :class="getModule(index).icon"></i>
            {{ index }}
          </span>

          <button
            v-for="item in group"
            :key="item.id"
            class="search-overlay__item"
            @click="goTo(item.url)"
          >
            <span class="search-overlay__item-label">{{ item.label }}</span>
            <span class="search-overlay__item-sub">{{
              item.sublabel ?? "⸺"
            }}</span>
          </button>
        </div>
      </div>
      <div v-if="noResults" class="search-overlay__results">
        <div class="search-overlay__group">
          <span class="search-overlay__notfound">
            {{ $t("globals.global_search.no_records") }}</span
          >
        </div>
      </div>
    </div>
  </div>
</template>
